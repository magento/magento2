<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

class ClassesScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Setup\Module\Di\Code\Reader\ClassesScanner();
    }

    public function testGetList()
    {
        $pathToScan = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files/app/code/Magento/SomeModule');
        $actual = $this->model->getList($pathToScan);
        $this->assertTrue(is_array($actual));
        $this->assertCount(5, $actual);
    }
}
