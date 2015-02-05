<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Legacy;

/**
 * Temporary test that will be removed in scope of MAGETWO-28356.
 * Test verifies obsolete usages in modules that were refactored to work with ResultInterface.
 */
class ObsoleteResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $obsoleteMethods = [];

    protected function setUp()
    {
        $this->obsoleteMethods = include __DIR__ . '/_files/obsolete_response_methods.php';
    }

    /**
     * Test verify that obsolete methods do not appear in refactored folders
     */
    public function testObsoleteResponseMethods()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                foreach ($this->obsoleteMethods as $method) {
                    $this->assertSame(
                        0,
                        preg_match('/(?<=[a-z\d_:]|->|function\s)' . $method . '\s*\(/iS', $content),
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
        $result = [];
        $appPath = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        $refactoredModules = $this->getRefactoredModules('refactored_modules*');
        foreach ($refactoredModules as $refactoredFolder) {
            $files = \Magento\Framework\Test\Utility\Files::init()->getFiles([$appPath . $refactoredFolder], '*.php');
            $result = array_merge($result, $files);
        }

        return \Magento\Framework\Test\Utility\Files::composeDataSets($result);
    }

    /**
     * @param string $filePattern
     * @return array
     */
    protected function getRefactoredModules($filePattern)
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/response_whitelist/' . $filePattern) as $file) {
            $fileData = include $file;
            $result = array_merge($result, $fileData);
        }
        return $result;
    }
}
