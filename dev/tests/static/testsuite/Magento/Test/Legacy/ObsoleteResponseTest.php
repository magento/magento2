<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Temporary test that will be removed in scope of MAGETWO-28356.
 * Test verifies obsolete usages in modules that were refactored to work with ResultInterface.
 */
class ObsoleteResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $obsoleteMethods = [];

    /**
     * @var array
     */
    protected $filesBlackList = [];

    /**
     * @var string
     */
    protected $appPath;

    protected function setUp()
    {
        $this->obsoleteMethods = include __DIR__ . '/_files/response/obsolete_response_methods.php';
        $this->filesBlackList = $this->getBlackList();
    }

    /**
     * Test verify that obsolete methods do not appear in refactored folders
     */
    public function testObsoleteResponseMethods()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                foreach ($this->obsoleteMethods as $method) {
                    $quotedMethod = preg_quote($method, '/');
                    $this->assertSame(
                        0,
                        preg_match('/(?<=[a-z\\d_:]|->|function\\s)' . $quotedMethod . '\\s*\\(/iS', $content),
                        "File: $file\nContains obsolete method: $method . "
                    );
                }
            },
            $this->modulesFilesDataProvider()
        );
    }

    /**
     * Return refactored files
     *
     * @return array
     */
    public function modulesFilesDataProvider()
    {
        $filesList = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($this->getFilesData('whitelist/refactored_modules*') as $refactoredModule) {
            if ($componentRegistrar->getPath(ComponentRegistrar::MODULE, $refactoredModule)) {
                $files = \Magento\Framework\App\Utility\Files::init()->getFiles(
                    [$componentRegistrar->getPath(ComponentRegistrar::MODULE, $refactoredModule)],
                    '*.php'
                );
                $filesList = array_merge($filesList, $files);
            }
        }

        $result = array_map('realpath', $filesList);
        $result = array_diff($result, $this->filesBlackList);
        return \Magento\Framework\App\Utility\Files::composeDataSets($result);
    }

    /**
     * @return array
     */
    protected function getBlackList()
    {
        $blackListFiles = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($this->getFilesData('blacklist/files_list*') as $fileInfo) {
            $blackListFiles[] = $componentRegistrar->getPath(ComponentRegistrar::MODULE, $fileInfo[0])
                . DIRECTORY_SEPARATOR . $fileInfo[1];
        }
        return $blackListFiles;
    }

    /**
     * @param string $filePattern
     * @return array
     */
    protected function getFilesData($filePattern)
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/response/' . $filePattern) as $file) {
            $fileData = include $file;
            $result = array_merge($result, $fileData);
        }
        return $result;
    }
}
