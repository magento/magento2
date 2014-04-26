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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Wishlist\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAddToCartUrl()
    {
        $url = 'http://magento.com/wishlist/index/index/wishlist_id/1/?___store=default';
        $encoded = 'encodedUrl';

        $coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $coreData->expects($this->any())
            ->method('urlEncode')
            ->with($url)
            ->will($this->returnValue($encoded));

        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $store->expects($this->any())
            ->method('getUrl')
            ->with('wishlist/index/cart', array('item' => '%item%', 'uenc' => $encoded))
            ->will($this->returnValue($url));

        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', array(), array(), '', false);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $urlBuilder = $this->getMock('Magento\Framework\UrlInterface\Proxy', array('getUrl'), array(), '', false);
        $urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('*/*/*', array('_current' => true, '_use_rewrite' => true, '_scope_to_url' => true))
            ->will($this->returnValue($url));

        $context = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var \Magento\Wishlist\Helper\Data $wishlistHelper */
        $wishlistHelper = $objectManager->getObject(
            'Magento\Wishlist\Helper\Data',
            array('context' => $context, 'storeManager' => $storeManager, 'coreData' => $coreData)
        );

        $this->assertEquals($url, $wishlistHelper->getAddToCartUrl('%item%'));
    }
}
