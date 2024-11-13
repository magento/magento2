<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\CodingStandard\Tool;

use Magento\TestFramework\CodingStandard\ToolInterface;
use SebastianBergmann\FileIterator\Facade;
use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Log\PMD;
use SebastianBergmann\PHPCPD\Log\Text;
use Symfony\Component\Finder\Finder;

/**
 * PHP Copy Paste Detector tool wrapper
 */
class CopyPasteDetector implements ToolInterface, BlacklistInterface
{
    /**
     * Minimum number of equal lines to identify a copy paste snippet
     */
    private const MIN_LINES = 13;

    /**
     * Destination file to write inspection report to
     *
     * @var string
     */
    private $reportFile;

    /**
     * List of paths to be excluded from tool run
     *
     * @var array
     */
    private $blacklist;

    /**
     * @param string $reportFile
     */
    public function __construct(string $reportFile)
    {
        $this->reportFile = $reportFile;
    }

    /**
     * @inheritdoc
     */
    public function setBlackList(array $blackList): void
    {
        $this->blacklist = $blackList;
    }

    /**
     * Whether the tool can be run in the current environment
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return bool
     */
    public function canRun(): bool
    {
        return class_exists(Detector::class)
            && class_exists(Facade::class)
            && class_exists(Finder::class);
    }

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @return bool
     */
    public function run(array $whiteList): bool
    {
        $clones = (new Detector(new DefaultStrategy()))->copyPasteDetection(
            (new Facade())->getFilesAsArray(
                $whiteList,
                '',
                '',
                $this->getExclude()
            ),
            self::MIN_LINES
        );

        (new PMD($this->reportFile))->processClones($clones);
        (new Text)->printResult($clones, false);

        return count($clones) === 0;
    }

    /**
     * Get exclude params from blacklist
     *
     * @return string[]
     */
    private function getExclude(): array
    {
        $exclude = [];
        $blacklistedDirs = [];
        $blacklistedFileNames = [];
        $blacklistedPatterns = [];
        foreach ($this->blacklist as $file) {
            $file = trim($file);
            if (!$file) {
                continue;
            }
            $realPath = realpath(BP . '/' . $file);
            if ($realPath === false) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if ($ext != '') {
                    $blacklistedFileNames[] = $file;
                } else {
                    $blacklistedPatterns[] = $file;
                }
                continue;
            }

            $exclude[] = [$realPath];
            $blacklistedDirs[] = $file;
        }

        foreach ($blacklistedPatterns as $pattern) {
            $files = $this->find($pattern, false, $blacklistedDirs);
            if (empty($files)) {
                continue;
            }
            $exclude[] = $files;
        }


        foreach ($blacklistedFileNames as $fileName) {
            $files = $this->find($fileName, true, $blacklistedDirs);
            if (empty($files)) {
                continue;
            }
            $exclude[] = $files;
        }

        return array_unique(array_merge(...$exclude));
    }

    /**
     * Find all files by pattern
     *
     * @param string $pattern
     * @param bool $searchFiles
     * @param array $excludePaths
     * @return array
     */
    private function find(string $pattern, bool $searchFiles, array $excludePaths): array
    {
        $finder = new Finder();
        $finder->in(BP);
        $finder->notPath($excludePaths);
        if ($searchFiles) {
            $finder->files();
            $finder->name($pattern);
        } else {
            $finder->path($pattern);
        }

        $result = [];
        foreach ($finder as $file) {
            $result[] = $file->getRealPath();
        }

        return $result;
    }
}
