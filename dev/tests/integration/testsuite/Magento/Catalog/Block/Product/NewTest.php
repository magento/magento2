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
namespace Magento\Catalog\Block\Product;

/**
 * Test class for \Magento\Catalog\Block\Product\New.
 *
 * @magentoDataFixture Magento/Catalog/_files/products_new.php
 */
class NewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\NewProduct
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Http\Context'
        )->setValue(
            \Magento\Customer\Helper\Data::CONTEXT_GROUP,
            \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID,
            \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID
        );
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Product\NewProduct'
        );
    }

    public function testGetCacheKeyInfo()
    {
        $info = $this->_block->getCacheKeyInfo();
        $keys = array_keys($info);

        /** order and values of cache key info elements is important */

        $this->assertSame(0, array_shift($keys));
        $this->assertEquals('CATALOG_PRODUCT_NEW', $info[0]);

        $this->assertSame(1, array_shift($keys));
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
            )->getStore()->getId(),
            $info[1]
        );

        $this->assertSame(2, array_shift($keys));

        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->getDesignTheme();

        $this->assertEquals($themeModel->getId() ?: null, $info[2]);

        $this->assertSame(3, array_shift($keys));
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Customer\Model\Session'
            )->getCustomerGroupId(),
            $info[3]
        );

        $this->assertSame('template', array_shift($keys));

        /**
         * This block is implemented without template by default (invalid).
         * Having the cache key fragment with empty value can potentially lead to caching bugs
         */
        $this->assertSame(4, array_shift($keys));
        $this->assertNotEquals('', $info[4]);
    }

    public function testSetGetProductsCount()
    {
        $this->assertEquals(
            \Magento\Catalog\Block\Product\NewProduct::DEFAULT_PRODUCTS_COUNT,
            $this->_block->getProductsCount()
        );
        $this->_block->setProductsCount(100);
        $this->assertEquals(100, $this->_block->getProductsCount());
    }

    public function testToHtml()
    {
        $this->assertEmpty($this->_block->getProductCollection());

        $this->_block->setProductsCount(5);
        $this->_block->setTemplate('product/widget/new/content/new_list.phtml');
        $this->_block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface')
        );

        $html = $this->_block->toHtml();
        $this->assertNotEmpty($html);
        $this->assertContains('New Product', $html);
        $this->assertInstanceOf(
            'Magento\Catalog\Model\Resource\Product\Collection',
            $this->_block->getProductCollection()
        );
    }
}
