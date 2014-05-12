<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Captcha\Model;

/**
 * Class \Magento\Captcha\Model\CronTest
 */
class CronTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Filesystem\DirectoryWriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directory;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \Magento\Captcha\Model\Cron
     */
    protected $_model;

    /**
     * @var int
     */
    public static $currentTime;

    /**
     * Create mocks and model
     */
    public function setUp()
    {
        $this->_helper = $this->getMock('Magento\Captcha\Helper\Data', array(), array(), '', false);
        $this->_adminHelper = $this->getMock('Magento\Captcha\Helper\Adminhtml\Data', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_directory = $this->getMock('Magento\Framework\Filesystem\Directory\Write', array(), array(), '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_directory)
        );

        $this->_model = new \Magento\Captcha\Model\Cron(
            $this->getMock('Magento\Captcha\Model\Resource\LogFactory', array(), array(), '', false),
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
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnValue(isset($website) ? array($website) : array())
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
            $this->returnValue(array($filename))
        );
        $this->_directory->expects($this->exactly($timesToCall))->method('isFile')->will($this->returnValue($isFile));
        $this->_directory->expects($this->any())->method('stat')->will($this->returnValue(array('mtime' => $mTime)));
        if ($mustDelete) {
            $this->_directory->expects($this->exactly($timesToCall))->method('delete')->with($filename);
        } else {
            $this->_directory->expects($this->never())->method('delete');
        }
        $this->_model->deleteExpiredImages();
    }

    /**
     * @return array
     */
    public function getExpiredImages()
    {
        $website = $this->getMock(
            'Magento\Store\Model\Website',
            array('__wakeup', 'getDefaultStore'),
            array(),
            '',
            false
        );
        $store = $this->getMock('Magento\Store\Model\Store', array('__wakeup'), array(), '', false);
        $website->expects($this->any())->method('getDefaultStore')->will($this->returnValue($store));
        $time = time();
        return array(
            array(null, true, 'test.png', 50, ($time - 60) / 60, true),
            array($website, false, 'test.png', 50, ($time - 60) / 60, false),
            array($website, true, 'test.jpg', 50, ($time - 60) / 60, false),
            array($website, true, 'test.png', 50, ($time - 20) / 60, false)
        );
    }
}

/**
 * Fix current time
 *
 * @return int
 */
function time()
{
    if (!isset(CronTest::$currentTime)) {
        CronTest::$currentTime = \time();
    }
    return CronTest::$currentTime;
}
