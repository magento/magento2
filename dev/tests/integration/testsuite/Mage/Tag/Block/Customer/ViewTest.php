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
 * @package     Mage_Tag
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Tag
 */
class Mage_Tag_Block_Customer_ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Tag_Block_Customer_View
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Tag_Block_Customer_View();
    }

    public function testGetMode()
    {
        $layout = new Mage_Core_Model_Layout;
        $layout->addBlock($this->_block, 'test');
        $expected = uniqid();
        $toolbar = new Mage_Core_Block_Text(array('current_mode' => $expected));
        $layout->addBlock($toolbar, 'toolbar', 'test');
        $this->assertEquals($expected, $this->_block->getMode());
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/product_with_image.php
     */
    public function testImage()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);

        $size = $this->_block->getImageSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getImageUrl($product));
        $this->assertStringEndsWith('magento_image.jpg', $this->_block->getImageUrl($product));
    }
}
