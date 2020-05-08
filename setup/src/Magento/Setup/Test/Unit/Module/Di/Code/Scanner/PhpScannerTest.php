<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Framework\Reflection\TypeProcessor;

require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Helper/Test.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/ElementFactory.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Model/DoubleColon.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Api/Data/SomeInterface.php';
require_once __DIR__ . '/../../_files/app/code/Magento/SomeModule/Model/StubWithAnonymousClass.php';

use Magento\Setup\Module\Di\Code\Scanner\PhpScanner;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $this->log = $this->createMock(Log::class);
        $this->scanner = new PhpScanner($this->log, new TypeProcessor());
        $this->testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
    }

    public function testCollectEntities()
    {
        $testFiles = [
            $this->testDir . '/app/code/Magento/SomeModule/Helper/Test.php',
            $this->testDir . '/app/code/Magento/SomeModule/Model/DoubleColon.php',
            $this->testDir . '/app/code/Magento/SomeModule/Api/Data/SomeInterface.php',
            $this->testDir . '/app/code/Magento/SomeModule/Model/StubWithAnonymousClass.php',
        ];

        $this->log->expects(self::at(0))
            ->method('add')
            ->with(
                4,
                'Magento\SomeModule\Module\Factory',
                'Invalid Factory for nonexistent class Magento\SomeModule\Module in file ' . $testFiles[0]
            );
        $this->log->expects(self::at(1))
            ->method('add')
            ->with(
                4,
                'Magento\SomeModule\Element\Factory',
                'Invalid Factory declaration for class Magento\SomeModule\Element in file ' . $testFiles[0]
            );

        $result = $this->scanner->collectEntities($testFiles);

        self::assertEquals(
            ['\\' . \Magento\Eav\Api\Data\AttributeExtensionInterface::class],
            $result
        );
    }
}
