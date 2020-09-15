<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Test\Unit\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @var Base|MockObject
     */
    private $model;

    /**
     * @var \ReflectionMethod
     */
    private $sanitizeMethod;

    protected function setUp(): void
    {
        $driverMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = new Base($driverMock);

        $class = new \ReflectionClass($this->model);
        $this->sanitizeMethod = $class->getMethod('sanitizeFileName');
        $this->sanitizeMethod->setAccessible(true);
    }

    public function testSanitizeEmpty()
    {
        $this->assertEquals('', $this->sanitizeMethod->invokeArgs($this->model, ['']));
    }

    public function testSanitizeSimpleFilename()
    {
        $this->assertEquals('custom.log', $this->sanitizeMethod->invokeArgs($this->model, ['custom.log']));
    }

    public function testSanitizeLeadingSlashFilename()
    {
        $this->assertEquals(
            'customfolder/custom.log',
            $this->sanitizeMethod->invokeArgs($this->model, ['/customfolder/custom.log'])
        );
    }

    public function testSanitizeParentLevelFolder()
    {
        $this->assertEquals(
            'var/hack/custom.log',
            $this->sanitizeMethod->invokeArgs($this->model, ['../../../var/hack/custom.log'])
        );
    }

    public function testSanitizeFileException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Filename expected to be a string');
        $this->sanitizeMethod->invokeArgs($this->model, [['filename' => 'notValid']]);
    }
}
