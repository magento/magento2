<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

/**
 * Class MediaTest
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\File
     */
    protected $_model;

    /**
     * @var \Magento\MediaStorage\Helper\File\Media
     */
    protected $_loggerMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_storageHelperMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_mediaHelperMock;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $_fileUtilityMock;

    protected function setUp()
    {
        $this->_loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->_storageHelperMock = $this->getMock(
            'Magento\MediaStorage\Helper\File\Storage\Database',
            [],
            [],
            '',
            false
        );
        $this->_mediaHelperMock = $this->getMock('Magento\MediaStorage\Helper\File\Media', [], [], '', false);
        $this->_fileUtilityMock = $this->getMock(
            'Magento\MediaStorage\Model\ResourceModel\File\Storage\File',
            [],
            [],
            '',
            false
        );

        $this->_model = new \Magento\MediaStorage\Model\File\Storage\File(
            $this->_loggerMock,
            $this->_storageHelperMock,
            $this->_mediaHelperMock,
            $this->_fileUtilityMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testCollectDataSuccess()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(['files' => ['value1', 'value2']])
        );
        $this->assertEmpty(array_diff($this->_model->collectData(0, 1), ['value1']));
    }

    public function testCollectDataFailureWrongType()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(['files' => ['value1', 'value2']])
        );
        $this->assertFalse($this->_model->collectData(0, 1, 'some-wrong-key'));
    }

    public function testCollectDataFailureEmptyDataWasGiven()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(['files' => []])
        );
        $this->assertFalse($this->_model->collectData(0, 1));
    }
}
