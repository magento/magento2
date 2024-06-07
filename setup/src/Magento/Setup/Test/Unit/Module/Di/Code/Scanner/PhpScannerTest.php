<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Framework\Reflection\TypeProcessor;

require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Helper/TestHelper.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/ElementFactory.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Model/DoubleColon.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Api/Data/SomeInterface.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Model/StubWithAnonymousClass.php';

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\Code\Scanner\PhpScanner;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PhpScannerTest extends TestCase
{
    /**
     * @var PhpScanner
     */
    private $scanner;

    /**
     * @var string
     */
    private $testDir;

    /**
     * @var Log|MockObject
     */
    private $log;

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
        $this->log = $this->createMock(Log::class);
        $this->scanner = new PhpScanner($this->log, new TypeProcessor());
        $this->testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
    }

    /**
     * @return void
     */
    public function testCollectEntities(): void
    {
        $testFiles = [
            $this->testDir . '/app/code/Magento/SomeModule/Helper/TestHelper.php',
            $this->testDir . '/app/code/Magento/SomeModule/Model/DoubleColon.php',
            $this->testDir . '/app/code/Magento/SomeModule/Api/Data/SomeInterface.php',
            $this->testDir . '/app/code/Magento/SomeModule/Model/StubWithAnonymousClass.php'
        ];

        $this->log
            ->method('add')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) {
                    if ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Module\Factory') {
                        return null;
                    } elseif ($arg1 == 4 && $arg2 == 'Magento\SomeModule\Element\Factory') {
                        return null;
                    }
                }
            );

        $result = $this->scanner->collectEntities($testFiles);

        self::assertEquals(
            ['\\' . \Magento\Eav\Api\Data\AttributeExtensionInterface::class],
            $result
        );
    }
}
