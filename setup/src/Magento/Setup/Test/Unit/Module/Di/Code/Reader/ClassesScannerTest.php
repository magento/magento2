<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Framework\App\Filesystem\DirectoryList;

class ClassesScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner
     */
    private $model;

    /**
     * the /var/generation directory realpath
     *
     * @var string
     */

    private $generation;

    protected function setUp()
    {
        $this->generation = realpath(__DIR__ . '/../../_files/var/generation');
        $mock = $this->getMockBuilder(DirectoryList::class)->disableOriginalConstructor()->setMethods(
            ['getPath']
        )->getMock();
        $mock->method('getPath')->willReturn($this->generation);
        $this->model = new \Magento\Setup\Module\Di\Code\Reader\ClassesScanner([], $mock);
    }

    public function testGetList()
    {
        $pathToScan = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files/app/code/Magento/SomeModule');
        $actual = $this->model->getList($pathToScan);
        $this->assertTrue(is_array($actual));
        $this->assertCount(5, $actual);
    }
}
