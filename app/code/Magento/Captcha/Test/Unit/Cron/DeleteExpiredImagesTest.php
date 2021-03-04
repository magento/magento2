<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Cron;

class DeleteExpiredImagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_helper;

    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_adminHelper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManager;

    /**
     * @var \Magento\Captcha\Cron\DeleteExpiredImages
     */
    protected $_deleteExpiredImages;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_directory;

    /**
     * @var int
     */
    public static $currentTime;

    /**
     * Create mocks and model
     */
    protected function setUp(): void
    {
        $this->_helper = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->_adminHelper = $this->createMock(\Magento\Captcha\Helper\Adminhtml\Data::class);
        $this->_filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->_directory = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->willReturn(
            $this->_directory
        );

        $this->_deleteExpiredImages = new \Magento\Captcha\Cron\DeleteExpiredImages(
            $this->_helper,
            $this->_adminHelper,
            $this->_filesystem,
            $this->_storeManager
        );
    }

    /**
     * @dataProvider getExpiredImages
     */
    public function testDeleteExpiredImages($website, $isFile, $filename, $mTime, $timeout)
    {
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
                $this->equalTo('timeout'),
                new \PHPUnit\Framework\Constraint\IsIdentical($website->getDefaultStore())
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
            $this->equalTo('timeout'),
            new \PHPUnit\Framework\Constraint\IsNull()
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

    /**
     * @return array
     */
    public function getExpiredImages()
    {
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['__wakeup', 'getDefaultStore']);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['__wakeup']);
        $website->expects($this->any())->method('getDefaultStore')->willReturn($store);
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
