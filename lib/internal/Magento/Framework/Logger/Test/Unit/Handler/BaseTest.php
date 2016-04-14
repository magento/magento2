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

    public function setUp()
    {
        $this->_model = new \Magento\Framework\Logger\Handler\Base(
            $this->getMock('Magento\Framework\Filesystem\DriverInterface')
        );
    }

    public function testSanitizeEmpty()
    {
        $this->assertEquals('', $this->_model->sanitizeFileName(''));
    }

    public function testSanitizeSimpleFilename()
    {
        $this->assertEquals('custom.log', $this->_model->sanitizeFileName('custom.log'));
    }

    public function testSanitizeLeadingSlashFilename()
    {
        $this->assertEquals('customfolder/custom.log', $this->_model->sanitizeFileName('/customfolder/custom.log'));
    }

    public function testSanitizeParentLevelFolder()
    {
        $this->assertEquals('var/hack/custom.log', $this->_model->sanitizeFileName('../../../var/hack/custom.log'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename expected to be a string
     */
    public function testSanitizeFileException()
    {
        $this->_model->sanitizeFileName(['filename' => 'notValid']);
    }
}