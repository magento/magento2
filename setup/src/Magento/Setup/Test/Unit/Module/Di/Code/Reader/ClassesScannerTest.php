<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use PHPUnit\Framework\TestCase;

class ClassesScannerTest extends TestCase
{
    /**
     * @var ClassesScanner
     */
    private $model;

    /**
     * the /var/generation directory realpath
     *
     * @var string
     */

    private $generation;

    protected function setUp(): void
    {
        $this->generation = realpath(__DIR__ . '/../../_files/var/generation');
        $mock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getPath']
            )->getMock();
        $mock->method('getPath')->willReturn($this->generation);
        $this->model = new ClassesScanner([], $mock);
    }

    public function testGetList()
    {
        $pathToScan = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files/app/code/Magento/SomeModule');
        $actual = $this->model->getList($pathToScan);
        $this->assertIsArray($actual);
        $this->assertCount(6, $actual);
    }
}
