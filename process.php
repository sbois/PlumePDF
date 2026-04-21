<?php
/**
 * PlumePDF — process.php
 * Reçoit des fichiers (images JPG/PNG/HEIC et/ou PDF), les assemble en un seul PDF,
 * puis compresse via Ghostscript selon le niveau choisi (medium / high).
 */

set_time_limit(300);
ini_set('memory_limit', '512M');

// ------- Helpers -------
function fail($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

function cleanup($paths) {
    foreach ($paths as $p) {
        if (is_string($p) && file_exists($p)) @unlink($p);
    }
}

function find_binary($candidates) {
    foreach ($candidates as $c) {
        if (is_executable($c)) return $c;
    }
    // Fallback via which (sur Mac, shell_exec peut renvoyer le chemin)
    foreach ($candidates as $c) {
        $name = basename($c);
        $out = trim(@shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null') ?: '');
        if ($out && is_executable($out)) return $out;
    }
    return null;
}

// ------- Détection des binaires -------
// Sur macOS avec Homebrew : /opt/homebrew/bin (Apple Silicon) ou /usr/local/bin (Intel)
$GS = find_binary([
    '/opt/homebrew/bin/gs',
    '/usr/local/bin/gs',
    '/usr/bin/gs',
    'gs'
]);

$MAGICK = find_binary([
    '/opt/homebrew/bin/magick',
    '/usr/local/bin/magick',
    '/opt/homebrew/bin/convert',
    '/usr/local/bin/convert',
    '/usr/bin/convert',
    'magick',
    'convert'
]);

if (!$GS)     fail("Ghostscript introuvable. Installez-le (brew install ghostscript) ou ajustez le chemin dans process.php.", 500);
if (!$MAGICK) fail("ImageMagick introuvable. Installez-le (brew install imagemagick) ou ajustez le chemin dans process.php.", 500);

// ------- Validation requête -------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Méthode non autorisée', 405);

// Détection du dépassement de post_max_size : PHP vide $_POST et $_FILES
// mais CONTENT_LENGTH reste renseigné dans l'en-tête de la requête.
$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMax = ini_get('post_max_size');
$uploadMax = ini_get('upload_max_filesize');
if (empty($_POST) && empty($_FILES) && $contentLength > 0) {
    fail(
        "Fichiers trop volumineux pour la configuration de PHP.\n" .
        "Taille envoyée : " . round($contentLength / 1048576, 1) . " Mo\n" .
        "Limite post_max_size : {$postMax}\n" .
        "Limite upload_max_filesize : {$uploadMax}\n\n" .
        "Augmentez ces deux valeurs dans php.ini puis redémarrez Apache.",
        413
    );
}

$compression = $_POST['compression'] ?? '';
if (!in_array($compression, ['medium', 'high'], true)) {
    fail('Niveau de compression invalide (reçu : "' . htmlspecialchars($compression) . '", attendu : medium ou high).');
}

if (empty($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    fail('Aucun fichier reçu.');
}

// Vérifier les erreurs d'upload spécifiques (taille individuelle trop grande)
foreach ($_FILES['files']['error'] as $i => $err) {
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        fail(
            "Le fichier \"" . htmlspecialchars($_FILES['files']['name'][$i]) . "\" dépasse la limite " .
            "upload_max_filesize ({$uploadMax}). Augmentez cette valeur dans php.ini.",
            413
        );
    }
}

// ------- Préparation session de travail -------
// On privilégie un dossier local au projet (droits garantis) plutôt que sys_get_temp_dir()
// qui peut pointer vers /var/folders/... protégé par SIP sur macOS.
$baseTmp = __DIR__ . '/tmp';
if (!is_dir($baseTmp)) {
    if (!@mkdir($baseTmp, 0777, true)) {
        // Fallback sur le tmp système
        $baseTmp = sys_get_temp_dir();
    }
}
// Essayer de rendre le dossier inscriptible si besoin
if (is_dir($baseTmp) && !is_writable($baseTmp)) {
    @chmod($baseTmp, 0777);
}

$workDir = $baseTmp . '/plumepdf_' . uniqid('', true);

if (!@mkdir($workDir, 0777, true)) {
    // Diagnostic détaillé pour comprendre la cause exacte
    $err = error_get_last();
    $user = function_exists('posix_getpwuid') && function_exists('posix_geteuid')
        ? (posix_getpwuid(posix_geteuid())['name'] ?? 'inconnu')
        : get_current_user();
    fail(
        "Impossible de créer le dossier de travail.\n" .
        "Chemin tenté : {$workDir}\n" .
        "Utilisateur Apache : {$user}\n" .
        "Parent existe : " . (is_dir($baseTmp) ? 'oui' : 'non') . "\n" .
        "Parent inscriptible : " . (is_writable($baseTmp) ? 'oui' : 'non') . "\n" .
        "Erreur PHP : " . ($err['message'] ?? 'aucune') . "\n\n" .
        "Solution : dans Terminal, exécutez :\n" .
        "  mkdir -p " . __DIR__ . "/tmp && chmod 777 " . __DIR__ . "/tmp",
        500
    );
}
$cleanupList = [$workDir];

// ------- Forcer TMPDIR pour les sous-processus (gs, ImageMagick) -------
// Sur macOS, Apache hérite d'un TMPDIR type /var/folders/xx/... protégé par SIP
// et inaccessible en écriture. On redirige vers notre dossier de travail local.
putenv('TMPDIR=' . $workDir);
putenv('TMP=' . $workDir);
putenv('TEMP=' . $workDir);
putenv('MAGICK_TMPDIR=' . $workDir);
// Préfixe à ajouter devant chaque commande shell pour transmettre l'env
$envPrefix = 'TMPDIR=' . escapeshellarg($workDir)
           . ' TMP=' . escapeshellarg($workDir)
           . ' TEMP=' . escapeshellarg($workDir)
           . ' MAGICK_TMPDIR=' . escapeshellarg($workDir) . ' ';

register_shutdown_function(function() use (&$cleanupList, $workDir) {
    foreach ($cleanupList as $p) {
        if (is_string($p) && file_exists($p) && !is_dir($p)) @unlink($p);
    }
    // Supprimer le dossier de travail récursivement
    if (is_dir($workDir)) {
        $it = new RecursiveDirectoryIterator($workDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $f) {
            if ($f->isDir()) @rmdir($f->getRealPath());
            else @unlink($f->getRealPath());
        }
        @rmdir($workDir);
    }
});

// ------- Tri et déplacement des fichiers -------
$uploaded = $_FILES['files'];
$numFiles = count($uploaded['name']);
$orderedPieces = []; // chaque élément : chemin vers un PDF intermédiaire

for ($i = 0; $i < $numFiles; $i++) {
    if ($uploaded['error'][$i] !== UPLOAD_ERR_OK) {
        fail("Erreur d'upload pour le fichier " . htmlspecialchars($uploaded['name'][$i]));
    }
    $tmp  = $uploaded['tmp_name'][$i];
    $orig = $uploaded['name'][$i];
    $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

    $safeBase = $workDir . '/in_' . $i;
    $safeSrc  = $safeBase . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
    if (!move_uploaded_file($tmp, $safeSrc)) {
        fail("Impossible de déplacer le fichier temporaire.");
    }

    $piecePdf = $workDir . '/piece_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.pdf';

    if ($ext === 'pdf') {
        // PDF direct : on garde tel quel (il sera concaténé puis compressé)
        if (!copy($safeSrc, $piecePdf)) fail("Erreur de copie PDF.");
    } else if (in_array($ext, ['jpg','jpeg','png','heic','heif'], true)) {
        // Convertir l'image en PDF via ImageMagick
        // -auto-orient : respecte l'orientation EXIF (important pour HEIC iPhone)
        // -density 150 : bonne qualité imprimable sans excès
        $cmd = $envPrefix . escapeshellarg($MAGICK);
        // Si c'est 'convert' seul (IMv6), la syntaxe est la même ; si c'est 'magick' (IMv7), on peut aussi faire "magick input output"
        $cmd .= ' ' . escapeshellarg($safeSrc)
              . ' -auto-orient'
              . ' -density 150'
              . ' ' . escapeshellarg($piecePdf);
        $out = []; $rc = 0;
        exec($cmd . ' 2>&1', $out, $rc);
        if ($rc !== 0 || !file_exists($piecePdf)) {
            fail("Conversion image échouée (" . htmlspecialchars($orig) . ") : " . implode(' | ', $out));
        }
    } else {
        fail("Format non supporté : " . htmlspecialchars($orig));
    }

    $orderedPieces[] = $piecePdf;
    @unlink($safeSrc);
}

if (empty($orderedPieces)) fail("Aucune pièce à assembler.");

// ------- Fusion de tous les PDF en un seul -------
$mergedPdf = $workDir . '/merged.pdf';

if (count($orderedPieces) === 1) {
    // Un seul fichier : pas besoin de fusionner
    if (!copy($orderedPieces[0], $mergedPdf)) fail("Erreur de copie du PDF.");
} else {
    // Fusion via Ghostscript (rapide et fiable)
    $cmd = $envPrefix . escapeshellarg($GS)
         . ' -dNOPAUSE -dBATCH -dQUIET'
         . ' -sDEVICE=pdfwrite'
         . ' -dCompatibilityLevel=1.5'
         . ' -sOutputFile=' . escapeshellarg($mergedPdf);
    foreach ($orderedPieces as $p) {
        $cmd .= ' ' . escapeshellarg($p);
    }
    $out = []; $rc = 0;
    exec($cmd . ' 2>&1', $out, $rc);
    if ($rc !== 0 || !file_exists($mergedPdf)) {
        fail("Fusion PDF échouée : " . implode(' | ', $out));
    }
}

// ------- Compression Ghostscript -------
// Profils :
//  - medium : /ebook  (150 dpi, bon équilibre qualité/poids)
//  - high   : /screen (72 dpi, très léger, idéal web/email)
$pdfSettings = $compression === 'high' ? '/screen' : '/ebook';

$finalPdf = $workDir . '/final.pdf';

$cmd = $envPrefix . escapeshellarg($GS)
     . ' -sDEVICE=pdfwrite'
     . ' -dCompatibilityLevel=1.5'
     . ' -dPDFSETTINGS=' . $pdfSettings
     . ' -dNOPAUSE -dQUIET -dBATCH'
     . ' -dDetectDuplicateImages=true'
     . ' -dCompressFonts=true'
     . ' -dSubsetFonts=true'
     . ' -sOutputFile=' . escapeshellarg($finalPdf)
     . ' ' . escapeshellarg($mergedPdf);

$out = []; $rc = 0;
exec($cmd . ' 2>&1', $out, $rc);

if ($rc !== 0 || !file_exists($finalPdf)) {
    fail("Compression échouée : " . implode(' | ', $out), 500);
}

// Vérifier que la compression n'a pas gonflé le fichier (cas rare : PDF déjà très compressé)
// Si le final est plus gros que le merged, on renvoie le merged (sauf en mode high où on force quand même)
if ($compression === 'medium' && filesize($finalPdf) > filesize($mergedPdf)) {
    copy($mergedPdf, $finalPdf);
}

// ------- Envoi au navigateur -------
$filename = 'plumepdf_' . ($compression === 'high' ? 'leger' : 'moyen') . '_' . date('Ymd_His') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($finalPdf));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($finalPdf);
exit;
