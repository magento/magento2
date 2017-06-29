<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Logger\Test\Unit\Handler;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Logger\Handler\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \ReflectionMethod
     */
    private $sanitizeMethod;

    protected function setUp()
    {
        $driverMock = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Framework\Logger\Handler\Base($driverMock);

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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename expected to be a string
     */
    public function testSanitizeFileException()
    {
        $this->sanitizeMethod->invokeArgs($this->model, [['filename' => 'notValid']]);
    }
}
