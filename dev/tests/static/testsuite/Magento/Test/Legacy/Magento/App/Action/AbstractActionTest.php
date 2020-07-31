<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Legacy\App\Action;

use Exception;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Utility\ClassNameExtractor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test newly created controllers must do not extend AbstractAction.
 */
class AbstractActionTest extends TestCase
{
    /**
     * @var ClassNameExtractor
     */
    private $classNameExtractor;

    /**
     * @var Files
     */
    private $fileUtilities;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->classNameExtractor = new ClassNameExtractor();
        $this->fileUtilities = Files::init();
    }

    /**
     * Test new
     *
    */
    public function testNewControllersDoNotExtendAbstractAction(): void
    {
        $files = $this->getTestFiles();
        $found = [];

        foreach ($files as $file) {
            $class = $this->classNameExtractor->getNameWithNamespace(file_get_contents($file[0]));

            if ($class) {
                try {
                    $classReflection = new  ReflectionClass($class);
                    if ($classReflection->isSubclassOf(AbstractAction::class)) {
                        $found[] = $class;
                    }
                } catch (Exception $exception) {
                    $this->addWarning('Skipped due to exception: ' . $class);
                }
            }
        }

        $this->assertEmpty(
            $found,
            "The following new controller(s) extend " . AbstractAction::class . "\r\n"
            . "All new controller classes must implement " . ActionInterface::class . " instead.\r\n"
            . print_r($found, true)
        );
    }

    /**
     * Provide files for test.
     *
     * @return array
     */
    private function getTestFiles(): array
    {
        $phpFiles = self::getAddedFilesList(self::getChangedFilesBaseDir());

        $phpFiles = Files::composeDataSets($phpFiles);
        $fileTypes = Files::INCLUDE_APP_CODE | Files::INCLUDE_LIBS | Files::AS_DATA_SET;
        return array_intersect_key($phpFiles, $this->fileUtilities->getPhpFiles($fileTypes));
    }

    /**
     * Provide list of new files.
     *
     * @param $changedFilesBaseDir
     *
     * @return string[]
     */
    private static function getAddedFilesList($changedFilesBaseDir)
    {
        return self::getFilesFromListFile(
            $changedFilesBaseDir,
            'changed_files*.added.*',
            function () {
                // if no list files, probably, this is the dev environment
                // phpcs:ignore Generic.PHP.NoSilencedErrors,Magento2.Security.InsecureFunction
                @exec('git diff --cached --name-only --diff-filter=A', $addedFiles);
                return $addedFiles;
            }
        );
    }

    /**
     * Read files from generated lists.
     *
     * @param string $listsBaseDir
     * @param string $listFilePattern
     * @param callable $noListCallback
     * @return string[]
     */
    private static function getFilesFromListFile(
        string $listsBaseDir,
        string $listFilePattern,
        callable $noListCallback
    ): array {
        $filesDefinedInList = [];

        $listFiles = glob($listsBaseDir . '/_files/' . $listFilePattern);
        if (!empty($listFiles)) {
            foreach ($listFiles as $listFile) {
                $filesDefinedInList[] = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }

           $filesDefinedInList = array_merge([], ...$filesDefinedInList);
        } else {
            $filesDefinedInList = call_user_func($noListCallback);
        }

        array_walk(
            $filesDefinedInList,
            function (&$file) {
                $file = BP . '/' . $file;
            }
        );

        $filesDefinedInList = array_values(array_unique($filesDefinedInList));

        return $filesDefinedInList;
    }

    /**
     * Returns base directory for generated lists.
     *
     * @return string
     */
    private static function getChangedFilesBaseDir(): string
    {
        return BP . DIRECTORY_SEPARATOR . 'dev' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'static' .
            DIRECTORY_SEPARATOR . 'testsuite' . DIRECTORY_SEPARATOR . 'Magento' . DIRECTORY_SEPARATOR . 'Test';
    }
}
