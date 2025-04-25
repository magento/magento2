<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\Code\Scanner\XmlScanner;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
        $objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                LoggerInterface::class,
                $this->createMock(LoggerInterface::class)
            ],
        ];
        $objectManagerHelper->prepareObjectManager($objects);
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
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($className) {
                if ($arg1 == 4 && $arg2 == $className && $arg3 == 'Invalid proxy class for ' .
                    substr($className, 0, -5)) {
                    return null;
                } elseif ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Model\Element\Proxy') {
                    return null;
                } elseif ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Model\Element2\Proxy') {
                    return null;
                } elseif ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Model\Nested\Element\Proxy') {
                    return null;
                } elseif ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Model\Nested\Element2\Proxy') {
                    return null;
                }
            });

        $actual = $this->model->collectEntities($this->testFiles);
        $expected = [];
        $this->assertEquals($expected, $actual);
    }
}
