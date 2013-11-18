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

namespace Magento\PageCache\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Observer
     */
    protected $_observer;

    protected function setUp()
    {
        $this->_observer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\PageCache\Model\Observer');
    }

    /**
     * @magentoConfigFixture current_store system/external_page_cache/enabled 1
     */
    public function testSetNoCacheCookie()
    {
        /** @var $cookie \Magento\Core\Model\Cookie */
        $cookie = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Cookie');
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
        $this->_observer->setNoCacheCookie(new \Magento\Event\Observer());
        $this->assertNotEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
    }

    /**
     * @magentoConfigFixture current_store system/external_page_cache/enabled 1
     */
    public function testDeleteNoCacheCookie()
    {
        /** @var $cookie \Magento\Core\Model\Cookie */
        $cookie = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Cookie');
        $cookie->set(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE, '1');
        $this->_observer->deleteNoCacheCookie(new \Magento\Event\Observer());
        $this->assertEmpty($cookie->get(\Magento\PageCache\Helper\Data::NO_CACHE_COOKIE));
    }
}
