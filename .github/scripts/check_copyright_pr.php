<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * PR copyright header checker.
 *
 * New files      — must carry the current year in the correct format for the repo edition.
 * Existing files — the normalized copyright header must be identical to the base branch;
 *                  the year must not be changed.
 *
 * Usage:
 *   php check_copyright_pr.php \
 *     --files   /tmp/copyright_changed.txt \
 *     --added   /tmp/copyright_added.txt \
 *     --base-dir <dir> \
 *     --template .github/scripts/copyright-template.txt
 */

// ── Argument parsing ──────────────────────────────────────────────────────────

$opts = getopt('', ['files:', 'added:', 'base-dir:', 'template:']);

$missing = [];
foreach (['files', 'base-dir', 'template'] as $req) {
    if (empty($opts[$req])) $missing[] = "--$req";
}
if ($missing) {
    fwrite(STDERR, "Error: missing required arguments: " . implode(', ', $missing) . "\n");
    fwrite(STDERR, "Usage: php check_copyright_pr.php --files <list> --added <list> --base-dir <dir> --template <path>\n");
    exit(1);
}

$filesPath    = $opts['files'];
$addedPath    = $opts['added'] ?? '';
$baseDir      = rtrim((string) $opts['base-dir'], '/');
$templatePath = $opts['template'];

if (!file_exists($filesPath)) {
    echo "No copyright-eligible changed files found — skipping.\n";
    exit(0);
}

$template        = trim((string) file_get_contents($templatePath));
$isPrivate       = str_contains($template, 'ADOBE CONFIDENTIAL');
$requiresNotice  = str_contains($template, 'NOTICE:');
$changedFiles = array_filter(array_map('trim', file($filesPath)));
$addedFiles   = ($addedPath && file_exists($addedPath))
    ? array_flip(array_filter(array_map('trim', file($addedPath))))
    : [];

$currentYear = (int) date('Y');
$issues  = [];
$checked = 0;
$skipped = 0;

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Normalize a copyright block for comparison: strip XML declarations, PHP
 * declare(), comment markers, whitespace and punctuation — mirrors the Python
 * _normalize_copyright() function.
 */
function normalizeHeader(string $text): string
{
    $text = preg_replace('/<\?xml[^?]*\?>/', '', $text);           // strip XML PI
    $text = preg_replace('/\bdeclare\s*\([^)]*\)\s*;?/', '', $text); // strip declare()
    return strtolower((string) preg_replace('/[\s#\/\*\-<>!?\[\]]+/', '', $text));
}

/**
 * Returns true when the file should be skipped entirely.
 */
function shouldExclude(string $filePath): bool
{
    static $allowedExts = ['php','phtml','html','js','xml','xsd','less','css','scss','graphqls'];

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) return true;

    // .github/ and vendor/ are always excluded
    if (preg_match('#^(\.github|vendor)/#', $filePath)) return true;

    // lib/web/ is excluded except lib/web/mage/ (Adobe-authored JS)
    if (preg_match('#^lib/web/#', $filePath) && !preg_match('#^lib/web/mage/#', $filePath)) return true;

    return false;
}

/**
 * Extract the first copyright block from file content.
 * Handles C-style (/** ... *\/), HTML (<!-- ... -->), and hash (# ...) blocks.
 * Returns the raw header text, or '' if no copyright found.
 */
function extractCopyrightBlock(string $content): string
{
    $lines = explode("\n", $content);
    $blockType     = null; // 'c', 'html', 'hash'
    $collected     = [];
    $foundCopyright = false;

    foreach ($lines as $i => $line) {
        $s = trim($line);

        // Line 0: skip PHP/XML opening tag but collect it
        if ($i === 0 && (str_starts_with($s, '<?php') || str_starts_with($s, '<?xml'))) {
            $collected[] = $line;
            continue;
        }

        if ($blockType === null) {
            if (str_starts_with($s, '/**') || str_starts_with($s, '/*')) {
                $blockType = 'c';
                $collected[] = $line;
                if (strlen($s) > 2 && str_ends_with($s, '*/')) {
                    $foundCopyright = stripos($s, 'copyright') !== false;
                    break;
                }
                continue;
            }
            if (str_starts_with($s, '<!--')) {
                $blockType = 'html';
                $collected[] = $line;
                if (strlen($s) > 4 && str_ends_with($s, '-->')) {
                    $foundCopyright = stripos($s, 'copyright') !== false;
                    break;
                }
                continue;
            }
            if (str_starts_with($s, '#')) {
                $blockType = 'hash';
                $collected[] = $line;
                if (stripos($s, 'copyright') !== false) $foundCopyright = true;
                continue;
            }
            // Empty lines and declare() before the copyright block are allowed
            if ($s === '') continue;
            if (preg_match('/^declare\s*\([^)]*\)\s*;?$/', $s)) continue;
            break;
        }

        if ($blockType === 'c') {
            $collected[] = $line;
            if (stripos($s, 'copyright') !== false) $foundCopyright = true;
            if (str_ends_with($s, '*/')) {
                if ($foundCopyright) break;
                $collected = []; $foundCopyright = false; $blockType = null; // non-copyright block, reset
            }
            continue;
        }

        if ($blockType === 'html') {
            $collected[] = $line;
            if (stripos($s, 'copyright') !== false) $foundCopyright = true;
            if (str_ends_with($s, '-->')) {
                if ($foundCopyright) break;
                $collected = []; $foundCopyright = false; $blockType = null;
            }
            continue;
        }

        if ($blockType === 'hash') {
            if (!str_starts_with($s, '#')) break; // end of hash block
            if (stripos($s, 'copyright') !== false) $foundCopyright = true;
            $collected[] = $line;
            continue;
        }
    }

    return $foundCopyright ? implode("\n", $collected) : '';
}

/**
 * Fetch the base-branch content of a file from the pre-fetched directory.
 * Returns null when the file did not exist in the base branch.
 */
function getBaseContent(string $filePath, string $baseDir): ?string
{
    $path = $baseDir . '/' . $filePath;
    return file_exists($path) ? (string) file_get_contents($path) : null;
}

/**
 * Build the expected copyright header for a given file type using the template.
 * The template is in /** ... *\/ block format.
 */
function buildExpectedHeader(string $template, string $filePath, int $year): string
{
    $tpl = str_replace('{{YEAR}}', (string) $year, $template);
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Template is already in /** */ form; adapt wrapping for the file type.
    // Strip outer /** and */ to get the inner lines.
    $inner = trim((string) preg_replace('/^\/\*\*\s*|\s*\*\/$/', '', $tpl));
    $innerLines = explode("\n", $inner);
    $bodyLines  = array_map(static fn(string $l): string => ltrim($l, ' *'), $innerLines);

    switch ($ext) {
        case 'php':
            return "<?php\n" . $tpl . "\n";
        case 'phtml':
            return "<?php\n" . $tpl . "\n?>\n";
        case 'xml':
        case 'xsd':
            $out = ['<?xml version="1.0"?>', '<!--'];
            foreach ($bodyLines as $l) $out[] = $l !== '' ? " * {$l}" : ' *';
            $out[] = ' -->';
            return implode("\n", $out) . "\n";
        case 'graphqls':
            $out = [];
            foreach ($bodyLines as $l) $out[] = $l !== '' ? "# {$l}" : '#';
            return implode("\n", $out) . "\n";
        case 'html':
            $out = ['<!--'];
            foreach ($bodyLines as $l) $out[] = $l !== '' ? " * {$l}" : ' *';
            $out[] = ' -->';
            return implode("\n", $out) . "\n";
        default:
            return $tpl . "\n";
    }
}

// ── Main loop ─────────────────────────────────────────────────────────────────

foreach ($changedFiles as $filePath) {
    if (shouldExclude($filePath)) {
        $skipped++;
        continue;
    }

    if (!is_file($filePath)) {
        $skipped++;
        continue;
    }

    $content = (string) file_get_contents($filePath);

    // Skip third-party files that contain no Magento/Adobe reference
    if (strpos($content, 'Magento') === false && strpos($content, 'Adobe') === false
        && strpos($filePath, 'Magento') === false && strpos($filePath, 'Adobe') === false
    ) {
        $skipped++;
        continue;
    }

    $checked++;
    $isNew         = isset($addedFiles[$filePath]);
    $currentHeader = extractCopyrightBlock($content);

    // ── Confidentiality format check (derived from template) ─────────────────
    $hasConfidential = str_contains($content, 'ADOBE CONFIDENTIAL');
    $hasNotice       = str_contains($content, 'NOTICE:');
    if ($isPrivate && !$hasConfidential) {
        $issues[] = [
            'file'   => $filePath,
            'reason' => 'Private repo: missing ADOBE CONFIDENTIAL header',
            'new'    => $isNew,
        ];
        continue;
    }
    if ($isPrivate && $requiresNotice && !$hasNotice) {
        $issues[] = [
            'file'   => $filePath,
            'reason' => 'Private repo: missing NOTICE block in copyright header',
            'new'    => $isNew,
        ];
        continue;
    }
    if (!$isPrivate && $hasConfidential) {
        $issues[] = [
            'file'   => $filePath,
            'reason' => 'Public repo: file must not contain ADOBE CONFIDENTIAL',
            'new'    => $isNew,
        ];
        continue;
    }

    if ($isNew) {
        // ── New file: must have a copyright header in current-year format ─────
        if ($currentHeader === '') {
            $issues[] = [
                'file'   => $filePath,
                'reason' => 'Missing copyright header',
                'new'    => true,
            ];
            continue;
        }
        $currentNorm  = normalizeHeader($currentHeader);
        $expected     = buildExpectedHeader($template, $filePath, $currentYear);
        $expectedNorm = normalizeHeader($expected);
        if ($expectedNorm !== '' && strpos($currentNorm, $expectedNorm) === false) {
            $issues[] = [
                'file'   => $filePath,
                'reason' => "New file: copyright header must use year {$currentYear} in the correct format",
                'new'    => true,
            ];
        }
    } else {
        // ── Existing file: compare against base; skip if base had no copyright ─
        $baseContent = getBaseContent($filePath, $baseDir);

        if ($baseContent === null) {
            // Modified file but base blob unavailable (partial clone fetch failure).
            // Skip rather than emit a false positive.
            echo "  [SKIP] {$filePath} — base content unavailable (partial clone fetch failure)\n";
            $skipped++;
            continue;
        }

        $baseHeader = extractCopyrightBlock($baseContent);
        if ($baseHeader === '') {
            continue;
        }

        if ($currentHeader === '') {
            $issues[] = [
                'file'   => $filePath,
                'reason' => 'Copyright header was removed in this PR',
                'new'    => false,
            ];
            continue;
        }

        $baseNorm    = normalizeHeader($baseHeader);
        $currentNorm = normalizeHeader($currentHeader);
        if ($baseNorm !== $currentNorm) {
            $issues[] = [
                'file'   => $filePath,
                'reason' => 'Copyright header was modified in this PR (year or content changed)',
                'new'    => false,
            ];
        }
    }
}

// ── Report ────────────────────────────────────────────────────────────────────

echo "Copyright check: {$checked} file(s) checked, {$skipped} skipped\n";

if (empty($issues)) {
    echo "All files have correct copyright headers\n";
    exit(0);
}

echo count($issues) . " file(s) have copyright issues:\n";
foreach ($issues as $issue) {
    $tag = $issue['new'] ? '[NEW]    ' : '[EXISTING]';
    echo "  {$tag} {$issue['file']}\n";
    echo "           {$issue['reason']}\n";
}
exit(1);
