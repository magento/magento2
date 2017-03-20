<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Cron;

class DeleteExpiredImagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adminHelper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \Magento\Captcha\Cron\DeleteExpiredImages
     */
    protected $_deleteExpiredImages;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directory;

    /**
     * @var int
     */
    public static $currentTime;

    /**
     * Create mocks and model
     */
    protected function setUp()
    {
        $this->_helper = $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false);
        $this->_adminHelper = $this->getMock(\Magento\Captcha\Helper\Adminhtml\Data::class, [], [], '', false);
        $this->_filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->_directory = $this->getMock(\Magento\Framework\Filesystem\Directory\Write::class, [], [], '', false);
        $this->_storeManager = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_directory)
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
    public function testDeleteExpiredImages($website, $isFile, $filename, $mTime, $timeout, $mustDelete)
    {
        $this->markTestSkipped("MAGETWO-34751: Hidden dependency");
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnValue(isset($website) ? [$website] : [])
        );
        if (isset($website)) {
            $this->_helper->expects(
                $this->once()
            )->method(
                'getConfig'
            )->with(
                $this->equalTo('timeout'),
                new \PHPUnit_Framework_Constraint_IsIdentical($website->getDefaultStore())
            )->will(
                $this->returnValue($timeout)
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
            new \PHPUnit_Framework_Constraint_IsNull()
        )->will(
            $this->returnValue($timeout)
        );

        $timesToCall = isset($website) ? 2 : 1;
        $this->_directory->expects(
            $this->exactly($timesToCall)
        )->method(
            'read'
        )->will(
            $this->returnValue([$filename])
        );
        $this->_directory->expects($this->exactly($timesToCall))->method('isFile')->will($this->returnValue($isFile));
        $this->_directory->expects($this->any())->method('stat')->will($this->returnValue(['mtime' => $mTime]));
        if ($mustDelete) {
            $this->_directory->expects($this->exactly($timesToCall))->method('delete')->with($filename);
        } else {
            $this->_directory->expects($this->never())->method('delete');
        }

        $this->_deleteExpiredImages->execute();
    }

    /**
     * @return array
     */
    public function getExpiredImages()
    {
        $website = $this->getMock(
            \Magento\Store\Model\Website::class,
            ['__wakeup', 'getDefaultStore'],
            [],
            '',
            false
        );
        $store = $this->getMock(\Magento\Store\Model\Store::class, ['__wakeup'], [], '', false);
        $website->expects($this->any())->method('getDefaultStore')->will($this->returnValue($store));
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
