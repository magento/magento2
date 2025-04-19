<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Cron;

use Magento\Captcha\Cron\DeleteExpiredImages;
use Magento\Captcha\Helper\Data;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteExpiredImagesTest extends TestCase
{
    /**
     * CAPTCHA helper
     *
     * @var Data|MockObject
     */
    protected $_helper;

    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data|MockObject
     */
    protected $_adminHelper;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystem;

    /**
     * @var StoreManager|MockObject
     */
    protected $_storeManager;

    /**
     * @var DeleteExpiredImages
     */
    protected $_deleteExpiredImages;

    /**
     * @var WriteInterface|MockObject
     */
    protected $_directory;

    /**
     * @var File|MockObject
     */
    protected $_fileInfo;

    /**
     * @var int
     */
    public static $currentTime;

    /**
     * Create mocks and model
     */
    protected function setUp(): void
    {
        $this->_helper = $this->createMock(Data::class);
        $this->_adminHelper = $this->createMock(\Magento\Captcha\Helper\Adminhtml\Data::class);
        $this->_filesystem = $this->createMock(Filesystem::class);
        $this->_directory = $this->createMock(Write::class);
        $this->_storeManager = $this->createMock(StoreManager::class);
        $this->_fileInfo = $this->createMock(File::class);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->willReturn(
            $this->_directory
        );

        $this->_deleteExpiredImages = new DeleteExpiredImages(
            $this->_helper,
            $this->_adminHelper,
            $this->_filesystem,
            $this->_storeManager,
            $this->_fileInfo
        );
    }

    /**
     * @dataProvider getExpiredImages
     */
    public function testDeleteExpiredImages($website, $isFile, $filename, $mTime, $timeout)
    {
        if ($website!=null) {
            $website = $website($this);
        }
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->willReturn(
            isset($website) ? [$website] : []
        );
        if (isset($website)) {
            $this->_helper->expects(
                $this->once()
            )->method(
                'getConfig'
            )->with(
                'timeout',
                new IsIdentical($website->getDefaultStore())
            )->willReturn(
                $timeout
            );
        } else {
            $this->_helper->expects($this->never())->method('getConfig');
        }
        $this->_adminHelper->expects(
            $this->once()
        )->method(
            'getConfig'
        )->with(
            'timeout',
            new IsNull()
        )->willReturn(
            $timeout
        );

        $timesToCall = isset($website) ? 2 : 1;
        $this->_directory->expects(
            $this->exactly($timesToCall)
        )->method(
            'read'
        )->willReturn(
            [$filename]
        );
        $this->_directory->expects($this->exactly($timesToCall))->method('isFile')->willReturn($isFile);
        $this->_directory->expects($this->any())->method('stat')->willReturn(['mtime' => $mTime]);

        $this->_deleteExpiredImages->execute();
    }

    protected function getMockForWebsiteClass()
    {
        $website = $this->createPartialMock(Website::class, ['__wakeup', 'getDefaultStore']);
        $store = $this->createPartialMock(Store::class, ['__wakeup']);
        $website->expects($this->any())->method('getDefaultStore')->willReturn($store);
        return $website;
    }

    /**
     * @return array
     */
    public static function getExpiredImages()
    {
        $website = static fn (self $testCase) => $testCase->getMockForWebsiteClass();
        $time = time();
        return [
            [null, true, 'test.png', 50, ($time - 60) / 60, true],
            [$website, false, 'test.png', 50, ($time - 60) / 60, false],
            [$website, true, 'test.jpg', 50, ($time - 60) / 60, false],
            [$website, true, 'test.png', 50, ($time - 20) / 60, false]
        ];
    }
}

/**
 * Fix current time
 *
 * @return int
 */
function time()
{
    if (!isset(DeleteExpiredImagesTest::$currentTime)) {
        DeleteExpiredImagesTest::$currentTime = \time();
    }
    return DeleteExpiredImagesTest::$currentTime;
}
