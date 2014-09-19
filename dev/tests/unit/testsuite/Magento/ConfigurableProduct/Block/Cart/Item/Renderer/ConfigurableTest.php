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
namespace Magento\ConfigurableProduct\Block\Cart\Item\Renderer;

use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as Renderer;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configManager;

    /** @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject */
    protected $_imageHelper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $productConfigMock;

    /** @var Renderer */
    protected $_renderer;

    protected function setUp()
    {
        parent::setUp();
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_configManager = $this->getMock('Magento\Framework\View\ConfigInterface', array(), array(), '', false);
        $this->_imageHelper = $this->getMock(
            'Magento\Catalog\Helper\Image',
            array('init', 'resize', '__toString'),
            array(),
            '',
            false
        );
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->productConfigMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Configuration',
            array(),
            array(),
            '',
            false
        );
        $this->_renderer = $objectManagerHelper->getObject(
            'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable',
            array(
                'viewConfig' => $this->_configManager,
                'imageHelper' => $this->_imageHelper,
                'scopeConfig' => $this->_scopeConfig,
                'productConfig' => $this->productConfigMock
            )
        );
    }

    public function testGetProductThumbnailUrl()
    {
        $url = 'pub/media/catalog/product/cache/1/thumbnail/75x/9df78eab33525d08d6e5fb8d27136e95/_/_/__green.gif';
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $configView = $this->getMock('Magento\Framework\Config\View', array('getVarValue'), array(), '', false);
        $configView->expects($this->any())->method('getVarValue')->will($this->returnValue(75));

        $this->_configManager->expects($this->any())->method('getViewConfig')->will($this->returnValue($configView));

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('isConfigurable', '__wakeup'),
            array(),
            '',
            false
        );
        $product->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));

        $childProduct = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getThumbnail', 'getDataByKey', '__wakeup'),
            array(),
            '',
            false
        );
        $childProduct->expects($this->any())->method('getThumbnail')->will($this->returnValue('/_/_/__green.gif'));

        $this->_imageHelper->expects($this->any())->method('init')->will($this->returnValue($this->_imageHelper));
        $this->_imageHelper->expects($this->any())->method('resize')->will($this->returnValue($this->_imageHelper));
        $this->_imageHelper->expects($this->any())->method('__toString')->will($this->returnValue($url));

        $arguments = array(
            'statusListFactory' => $this->getMock(
                'Magento\Sales\Model\Status\ListFactory',
                array(),
                array(),
                '',
                false
            ),
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false),
            'itemOptionFactory' => $this->getMock(
                'Magento\Sales\Model\Quote\Item\OptionFactory',
                array(),
                array(),
                '',
                false
            ),
            'priceCurrency' => $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock()
        );
        $childItem = $objectManagerHelper->getObject('Magento\Sales\Model\Quote\Item', $arguments);
        $childItem->setData('product', $childProduct);

        $item = $objectManagerHelper->getObject('Magento\Sales\Model\Quote\Item', $arguments);
        $item->setData('product', $product);
        $item->addChild($childItem);

        $this->_renderer->setItem($item);

        $configurableUrl = $this->_renderer->getProductThumbnailUrl();
        $this->assertNotNull($configurableUrl);
    }

    /**
     * Child thumbnail is available and config option is not set to use parent thumbnail.
     */
    public function testGetProductForThumbnail()
    {
        $childHasThumbnail = true;
        $useParentThumbnail = false;
        $products = $this->_initProducts($childHasThumbnail, $useParentThumbnail);

        $productForThumbnail = $this->_renderer->getProductForThumbnail();
        $this->assertSame(
            $products['childProduct'],
            $productForThumbnail,
            'Child product was expected to be returned.'
        );
    }

    /**
     * Child thumbnail is not available and config option is not set to use parent thumbnail.
     */
    public function testGetProductForThumbnailChildThumbnailNotAvailable()
    {
        $childHasThumbnail = false;
        $useParentThumbnail = false;
        $products = $this->_initProducts($childHasThumbnail, $useParentThumbnail);

        $productForThumbnail = $this->_renderer->getProductForThumbnail();
        $this->assertSame(
            $products['parentProduct'],
            $productForThumbnail,
            'Parent product was expected to be returned.'
        );
    }

    /**
     * Child thumbnail is available and config option is set to use parent thumbnail.
     */
    public function testGetProductForThumbnailConfigUseParent()
    {
        $childHasThumbnail = true;
        $useParentThumbnail = true;
        $products = $this->_initProducts($childHasThumbnail, $useParentThumbnail);

        $productForThumbnail = $this->_renderer->getProductForThumbnail();
        $this->assertSame(
            $products['parentProduct'],
            $productForThumbnail,
            'Parent product was expected to be returned ' .
            'if "checkout/cart/configurable_product_image option" is set to "parent" in system config.'
        );
    }

    /**
     * Initialize parent configurable product and child product.
     *
     * @param bool $childHasThumbnail
     * @param bool $useParentThumbnail
     * @return \Magento\Catalog\Model\Product[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function _initProducts($childHasThumbnail = true, $useParentThumbnail = false)
    {
        /** Set option which can force usage of parent product thumbnail when configurable product is displayed */
        $thumbnailToBeUsed = $useParentThumbnail
            ? ThumbnailSource::OPTION_USE_PARENT_IMAGE
            : ThumbnailSource::OPTION_USE_OWN_IMAGE;
        $this->_scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            Renderer::CONFIG_THUMBNAIL_SOURCE
        )->will(
            $this->returnValue($thumbnailToBeUsed)
        );

        /** Initialized parent product */
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $parentProduct */
        $parentProduct = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        /** Initialize child product */
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $childProduct */
        $childProduct = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getThumbnail', '__wakeup'),
            array(),
            '',
            false
        );
        $childThumbnail = $childHasThumbnail ? 'thumbnail.jpg' : 'no_selection';
        $childProduct->expects($this->any())->method('getThumbnail')->will($this->returnValue($childThumbnail));

        /** Mock methods which return parent and child products */
        /** @var \Magento\Sales\Model\Quote\Item\Option|\PHPUnit_Framework_MockObject_MockObject $itemOption */
        $itemOption = $this->getMock('Magento\Sales\Model\Quote\Item\Option', array(), array(), '', false);
        $itemOption->expects($this->any())->method('getProduct')->will($this->returnValue($childProduct));
        /** @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($parentProduct));
        $item->expects(
            $this->any()
        )->method(
            'getOptionByCode'
        )->with(
            'simple_product'
        )->will(
            $this->returnValue($itemOption)
        );
        $this->_renderer->setItem($item);

        return array('parentProduct' => $parentProduct, 'childProduct' => $childProduct);
    }

    public function testGetOptionList()
    {
        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item\AbstractItem', array(), array(), '', false);
        $this->_renderer->setItem($itemMock);
        $this->productConfigMock->expects($this->once())->method('getOptions')->with($itemMock);
        $this->_renderer->getOptionList();
    }

    public function testGetIdentities()
    {
        $productTags = array('catalog_product_1');
        $product = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $product->expects($this->exactly(2))->method('getIdentities')->will($this->returnValue($productTags));
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $item->expects($this->exactly(2))->method('getProduct')->will($this->returnValue($product));
        $this->_renderer->setItem($item);
        $this->assertEquals(array_merge($productTags, $productTags), $this->_renderer->getIdentities());
    }
}
