<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Logger\Test\Unit\Handler;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Logger\Handler\Base */
    protected $_model;

    protected $_sanitizeMethod;

    public function setUp()
    {
        $this->_model = new \Magento\Framework\Logger\Handler\Base(
            $this->getMock('Magento\Framework\Filesystem\DriverInterface')
        );

        $class = new \ReflectionClass($this->_model);
        $this->_sanitizeMethod = $class->getMethod('sanitizeFileName');
        $this->_sanitizeMethod->setAccessible(true);
    }

    public function testSanitizeEmpty()
    {
        $this->assertEquals('', $this->_sanitizeMethod->invokeArgs($this->_model, ['']));
    }

    public function testSanitizeSimpleFilename()
    {
        $this->assertEquals('custom.log', $this->_sanitizeMethod->invokeArgs($this->_model, ['custom.log']));
    }

    public function testSanitizeLeadingSlashFilename()
    {
        $this->assertEquals(
            'customfolder/custom.log',
            $this->_sanitizeMethod->invokeArgs($this->_model, ['/customfolder/custom.log'])
        );
    }

    public function testSanitizeParentLevelFolder()
    {
        $this->assertEquals(
            'var/hack/custom.log',
            $this->_sanitizeMethod->invokeArgs($this->_model, ['../../../var/hack/custom.log'])
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename expected to be a string
     */
    public function testSanitizeFileException()
    {
        $this->_sanitizeMethod->invokeArgs($this->_model, [['filename' => 'notValid']]);
    }
}
