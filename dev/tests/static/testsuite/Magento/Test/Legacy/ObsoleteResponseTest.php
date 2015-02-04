<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Temporary test that will be removed in scope of MAGETWO-28356.
 * Test verifies obsolete usages in controllers that were refactored to work with ResultInterface.
 */
namespace Magento\Test\Legacy;

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
    public function testControllersMethods()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                foreach ($this->obsoleteMethods as $method) {
                    $this->assertSame(
                        0,
                        preg_match('/(?<=[a-z\d_:]|->|function\s)' . $method . '\s*\(/iS', $content),
                        "Controller: $file\nContains obsolete method: $method . "
                    );
                }
            },
            $this->controllersFilesDataProvider()
        );
    }

    /**
     * Return controllers files list
     *
     * @return array
     */
    public function controllersFilesDataProvider()
    {
        $result = [];
        $appPath = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        $refactoredControllers = include __DIR__ . '/_files/whitelist/refactored_controllers.php';
        foreach ($refactoredControllers as $refactoredFolder) {
            $files = \Magento\Framework\Test\Utility\Files::init()->getFiles([$appPath . $refactoredFolder], '*.php');
            $result = array_merge($result, $files);
        }

        return \Magento\Framework\Test\Utility\Files::composeDataSets($result);
    }
}

