<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Helper\File\Media;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\File;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MediaTest extends TestCase
{
    /**
     * @var File
     */
    protected $_model;

    /**
     * @var Media
     */
    protected $_loggerMock;

    /**
     * @var Database
     */
    protected $_storageHelperMock;

    /**
     * @var DateTime
     */
    protected $_mediaHelperMock;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $_fileUtilityMock;

    protected function setUp(): void
    {
        $this->_loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->_storageHelperMock = $this->createMock(Database::class);
        $this->_mediaHelperMock = $this->createMock(Media::class);
        $this->_fileUtilityMock = $this->createMock(\Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class);

        $this->_model = new File(
            $this->_loggerMock,
            $this->_storageHelperMock,
            $this->_mediaHelperMock,
            $this->_fileUtilityMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testCollectDataSuccess()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->willReturn(
            ['files' => ['value1', 'value2']]
        );
        $this->assertEmpty(array_diff($this->_model->collectData(0, 1), ['value1']));
    }

    public function testCollectDataFailureWrongType()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->willReturn(
            ['files' => ['value1', 'value2']]
        );
        $this->assertFalse($this->_model->collectData(0, 1, 'some-wrong-key'));
    }

    public function testCollectDataFailureEmptyDataWasGiven()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->willReturn(
            ['files' => []]
        );
        $this->assertFalse($this->_model->collectData(0, 1));
    }
}
