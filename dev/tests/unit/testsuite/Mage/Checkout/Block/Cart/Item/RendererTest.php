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
 * @package     Mage_Checkout
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Checkout_Block_Cart_Item_RendererTest extends PHPUnit_Framework_TestCase
{
    public function testGetProductThumbnailUrlForConfigurable()
    {
        $url = 'pub/media/catalog/product/cache/1/thumbnail/75x/9df78eab33525d08d6e5fb8d27136e95/_/_/__green.gif';
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);

        $configView = $this->getMock('Magento_Config_View', array('getVarValue'), array(), '', false);
        $configView->expects($this->any())->method('getVarValue')->will($this->returnValue(75));

        $filesystem = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor()->getMock();
        $designPackage = $this->getMock('Mage_Core_Model_Design_Package', array('getViewConfig'), array($filesystem));
        $designPackage->expects($this->any())->method('getViewConfig')->will($this->returnValue($configView));

        $configurable = $objectManagerHelper->getBlock('Mage_Checkout_Block_Cart_Item_Renderer_Configurable',
            array('designPackage' => $designPackage));

        $product = $this->getMock('Mage_Catalog_Model_Product', array('isConfigurable'), array(), '', false);
        $product->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));

        $childProduct =
            $this->getMock('Mage_Catalog_Model_Product', array('getThumbnail', 'getDataByKey'), array(), '', false);
        $childProduct->expects($this->any())->method('getThumbnail')->will($this->returnValue('/_/_/__green.gif'));

        $childItem = $objectManagerHelper->getModel('Mage_Sales_Model_Quote_Item');
        $childItem->setData('product', $childProduct);

        $item = $objectManagerHelper->getModel('Mage_Sales_Model_Quote_Item');
        $item->setData('product', $product);
        $item->addChild($childItem);

        $helperImage = $this->getMock('Mage_Catalog_Helper_Image', array('init', 'resize', '__toString'));
        $helperImage->expects($this->any())->method('init')->will($this->returnValue($helperImage));
        $helperImage->expects($this->any())->method('resize')->will($this->returnValue($helperImage));
        $helperImage->expects($this->any())->method('__toString')->will($this->returnValue($url));

        $layout = $configurable->getLayout();
        $layout->expects($this->any())->method('helper')->will($this->returnValue($helperImage));

        $configurable->setItem($item);

        $configurableUrl = $configurable->getProductThumbnailUrl();
        $this->assertNotNull($configurableUrl);
    }
}
