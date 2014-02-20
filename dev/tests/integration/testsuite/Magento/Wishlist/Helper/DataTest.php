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
 * @package     Magento_Wishlist
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Wishlist\Helper;

class DataTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Data
     */
    private $wishlistHelper;

    /**
     * @var \Magento\ObjectManager
     */
    private $objectManager;

    /**
     * Get requrer instance
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->wishlistHelper = $this->objectManager->get('Magento\Wishlist\Helper\Data');
    }

    /**
     * Clear wishlist helper property
     */
    protected function tearDown()
    {
        $this->_wishlistHelper = null;
    }

    public function testGetAddParams()
    {
        $product = $this->objectManager->get('Magento\Catalog\Model\Product');
        $product->setId(11);
        $json = $this->wishlistHelper->getAddParams($product);
        $params = (array) json_decode($json);
        $data = (array) $params['data'];
        $this->assertEquals('11', $data['product']);
        $this->assertArrayHasKey('form_key', $data);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/add/',
            $params['action']
        );
    }

    public function testGetMoveFromCartParams()
    {
        $json = $this->wishlistHelper->getMoveFromCartParams(11);
        $params = (array) json_decode($json);
        $data = (array) $params['data'];
        $this->assertEquals('11', $data['item']);
        $this->assertArrayHasKey('form_key', $data);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/fromcart/',
            $params['action']
        );
    }

    public function testGetUpdateParams()
    {
        $product = $this->objectManager->get('Magento\Catalog\Model\Product');
        $product->setId(11);
        $product->setWishlistItemId(15);
        $json = $this->wishlistHelper->getUpdateParams($product);
        $params = (array) json_decode($json);
        $data = (array) $params['data'];
        $this->assertEquals('11', $data['product']);
        $this->assertEquals('15', $data['id']);
        $this->assertArrayHasKey('form_key', $data);
        $this->assertArrayHasKey('uenc', $data);
        $this->assertStringEndsWith(
            'wishlist/index/updateItemOptions/',
            $params['action']
        );
    }

}
