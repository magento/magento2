<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\XmlScanner;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class XmlScannerTest extends TestCase
{
    /**
     * @var XmlScanner
     */
    private XmlScanner $model;

    /**
     * @var MockObject
     */
    private Log $logMock;

    /**
     * @var array
     */
    private array $testFiles = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->logMock = $this->createMock(Log::class);
        $this->model = new XmlScanner($this->logMock);
        $testDir = __DIR__ . '/../../' . '/_files';
        $this->testFiles = [
            $testDir . '/app/code/Magento/SomeModule/etc/adminhtml/system.xml',
            $testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $testDir . '/app/code/Magento/SomeModule/view/frontend/default.xml',
        ];
        require_once  __DIR__ . '/../../_files/app/code/Magento/SomeModule/Element.php';
        require_once  __DIR__ . '/../../_files/app/code/Magento/SomeModule/NestedElement.php';
    }

    /**
     * @return void
     */
    public function testCollectEntities(): void
    {
        $className = 'Magento\Store\Model\Config\Invalidator\Proxy';
        $this->logMock
            ->method('add')
            ->withConsecutive(
                [
                    4,
                    $className,
                    'Invalid proxy class for ' . substr($className, 0, -5)
                ],
                [
                    4,
                    'Magento\SomeModule\Model\Element\Proxy',
                    'Invalid proxy class for ' . substr('Magento\SomeModule\Model\Element\Proxy', 0, -5)
                ],
                [
                    4,
                    'Magento\SomeModule\Model\Element2\Proxy',
                    'Invalid proxy class for ' . substr('Magento\SomeModule\Model\Element2\Proxy', 0, -5)
                ],
                [
                    4,
                    'Magento\SomeModule\Model\Nested\Element\Proxy',
                    'Invalid proxy class for ' . substr('Magento\SomeModule\Model\Nested\Element\Proxy', 0, -5)
                ],
                [
                    4,
                    'Magento\SomeModule\Model\Nested\Element2\Proxy',
                    'Invalid proxy class for ' . substr('Magento\SomeModule\Model\Nested\Element2\Proxy', 0, -5)
                ],
            );
        $actual = $this->model->collectEntities($this->testFiles);
        $expected = [];
        $this->assertEquals($expected, $actual);
    }
}
