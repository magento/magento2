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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_TIME_ZONE = 'America/New_York';

    const TIME_FORMAT_SHORT_ISO = 'h:mm a';

    const DATETIME_FORMAT_SHORT = 'n/j/y g:i A';

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_storeManager;

    /**
     * @var \DateTime
     */
    protected $_dateTime;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_app = $this->getMock(
            '\Magento\Core\Model\App',
            array('getCache', 'getLowLevelFrontend', 'getStore'),
            array(),
            '',
            false
        );

        $this->_storeManager = $this->getMock(
            '\Magento\Core\Model\StoreManager',
            array('getStore', 'getConfig'),
            array(),
            '',
            false
        );

        $this->_dateTime = new \DateTime;
        $this->_dateTime->setTimezone(new \DateTimeZone(self::DEFAULT_TIME_ZONE));
    }

    public function testFormatDate()
    {
        /** @var $locale \Magento\Core\Model\Locale */
        $locale = $this->_objectManager->getObject(
            '\Magento\Core\Model\Locale',
            $this->_getConstructArgsForDateFormatting()
        );

        $this->assertEquals(
            $this->_dateTime->format(self::DATETIME_FORMAT_SHORT),
            $locale->formatDate(null, 'short', true)
        );
    }

    public function testFormatTime()
    {
        /** @var $locale \Magento\Core\Model\Locale */
        $locale = $this->_objectManager->getObject(
            '\Magento\Core\Model\Locale',
            $this->_getConstructArgsForDateFormatting()
        );

        $this->assertEquals(
            $this->_dateTime->format(self::DATETIME_FORMAT_SHORT), $locale->formatTime(null, 'short', true)
        );

        $zendDate = new \Zend_Date($this->_dateTime->format('U'));
        $this->assertEquals(
            $zendDate->toString(self::TIME_FORMAT_SHORT_ISO),
            $locale->formatTime($zendDate, 'short')
        );
    }

    protected function _getConstructArgsForDateFormatting()
    {
        $this->_app->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($this->_app));

        $cache = $this->getMock('Zend_Cache_Core');
        $this->_app->expects($this->once())
            ->method('getLowLevelFrontend')
            ->will($this->returnValue($cache));

        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->_storeManager));

        $this->_storeManager->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue(self::DEFAULT_TIME_ZONE));

        return array('app' => $this->_app, 'storeManager' => $this->_storeManager);
    }
}
