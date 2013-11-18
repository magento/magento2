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
 * @package     Magento_Checkout
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Cart\Item;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProductThumbnailUrlForConfigurable()
    {
        $url = 'pub/media/catalog/product/cache/1/thumbnail/75x/9df78eab33525d08d6e5fb8d27136e95/_/_/__green.gif';
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $configView = $this->getMock('Magento\Config\View', array('getVarValue'), array(), '', false);
        $configView->expects($this->any())->method('getVarValue')->will($this->returnValue(75));

        $configManager = $this->getMock('Magento\View\ConfigInterface', array(), array(), '', false);
        $configManager->expects($this->any())->method('getViewConfig')->will($this->returnValue($configView));

        $product = $this->getMock('Magento\Catalog\Model\Product', array('isConfigurable'), array(), '', false);
        $product->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));

        $childProduct = $this->getMock(
            'Magento\Catalog\Model\Product', array('getThumbnail', 'getDataByKey'), array(), '', false
        );
        $childProduct->expects($this->any())->method('getThumbnail')->will($this->returnValue('/_/_/__green.gif'));

        $helperImage = $this->getMock('Magento\Catalog\Helper\Image',
            array('init', 'resize', '__toString'), array(), '', false
        );
        $helperImage->expects($this->any())->method('init')->will($this->returnValue($helperImage));
        $helperImage->expects($this->any())->method('resize')->will($this->returnValue($helperImage));
        $helperImage->expects($this->any())->method('__toString')->will($this->returnValue($url));

        $helperFactory = $this->getMock(
            'Magento\Core\Model\Factory\Helper', array('get'), array(), '', false, false
        );
        $helperFactory->expects($this->any())
            ->method('get')
            ->with('Magento\Catalog\Helper\Image', array())
            ->will($this->returnValue($helperImage));

        $arguments = array(
            'statusListFactory' => $this->getMock(
                'Magento\Sales\Model\Status\ListFactory', array(), array(), '', false
            ),
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false),
            'itemOptionFactory' => $this->getMock(
                'Magento\Sales\Model\Quote\Item\OptionFactory', array(), array(), '', false
            ),
        );
        $childItem = $objectManagerHelper->getObject('Magento\Sales\Model\Quote\Item', $arguments);
        $childItem->setData('product', $childProduct);

        $item = $objectManagerHelper->getObject('Magento\Sales\Model\Quote\Item', $arguments);
        $item->setData('product', $product);
        $item->addChild($childItem);

        $configurable = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Item\Renderer\Configurable',
            array(
                'viewConfig' => $configManager,
                'helperFactory' => $helperFactory,
            ));

        $layout = $configurable->getLayout();
        $layout->expects($this->any())->method('helper')->will($this->returnValue($helperImage));

        $configurable->setItem($item);

        $configurableUrl = $configurable->getProductThumbnailUrl();
        $this->assertNotNull($configurableUrl);
    }
}
