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
 * @package     Magento_PageCache
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\PageCache\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\PageCache\Helper\Data');
    }

    public function testSetNoCacheCookie()
    {
        /** @var $cookie \Magento\Core\Model\Cookie */
        $cookie = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Cookie');
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
        $this->_helper->setNoCacheCookie();
        $this->assertNotEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
    }

    public function testRemoveNoCacheCookie()
    {
        /** @var $cookie \Magento\Core\Model\Cookie */
        $cookie = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Cookie');
        $this->_helper->setNoCacheCookie();
        $this->_helper->removeNoCacheCookie();
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
    }

    public function testLockUnlockNoCacheCookie()
    {
        /** @var $cookie \Magento\Core\Model\Cookie */
        $cookie = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Cookie');
        $this->_helper->setNoCacheCookie();
        $this->assertNotEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));

        $this->_helper->lockNoCacheCookie();
        $this->_helper->removeNoCacheCookie();
        $this->assertNotEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));

        $this->_helper->unlockNoCacheCookie();
        $this->_helper->removeNoCacheCookie();
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));

        $this->_helper->lockNoCacheCookie();
        $this->_helper->setNoCacheCookie();
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
    }
}
