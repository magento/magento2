<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Integrity;

use Composer\Package\Archiver\ArchivableFilesFinder;
use PHPUnit\Framework\TestCase;

class ComposerArchiveTest extends TestCase
{
    private const TAR_NAME_LIMIT = 100;
    private const TAR_PREFIX_LIMIT = 155;
    private const RUNTIME_DIRECTORY = 'dev/tests/integration/tmp/';

    public function testArchivableFilesFitTarFormat(): void
    {
        $composerJson = json_decode(file_get_contents(BP . '/composer.json'), true);
        $finder = new ArchivableFilesFinder(BP, $composerJson['archive']['exclude'] ?? []);

        $tooLong = [];
        foreach ($finder as $file) {
            $path = ltrim(substr($file->getPathname(), strlen(BP)), '/');
            if (str_starts_with($path, self::RUNTIME_DIRECTORY) || $this->fitsTarFormat($path)) {
                continue;
            }
            $tooLong[] = $path;
        }

        $this->assertSame(
            [],
            $tooLong,
            'Files that cannot be stored in a tar archive made by "composer archive". '
            . 'Shorten the name or add the file to "archive.exclude" in composer.json.'
        );
    }

    private function fitsTarFormat(string $path): bool
    {
        if (strlen($path) <= self::TAR_NAME_LIMIT) {
            return true;
        }
        for ($slash = strpos($path, '/'); $slash !== false; $slash = strpos($path, '/', $slash + 1)) {
            if ($slash <= self::TAR_PREFIX_LIMIT && strlen($path) - $slash - 1 <= self::TAR_NAME_LIMIT) {
                return true;
            }
        }
        return false;
    }
}
