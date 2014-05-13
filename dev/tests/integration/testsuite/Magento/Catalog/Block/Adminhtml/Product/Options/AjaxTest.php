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
namespace Magento\Catalog\Block\Adminhtml\Product\Options;

/**
 * @magentoAppArea adminhtml
 */
class AjaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax
     */
    protected $_block = null;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Options\Ajax'
        );
    }

    public function testToHtmlWithoutProducts()
    {
        $this->assertEquals(json_encode(array()), $this->_block->toHtml());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     */
    public function testToHtml()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('import_option_products', array(1));
        $result = json_decode($this->_block->toHtml(), true);
        $this->assertEquals('test_option_code_1', $result[0]['title']);
    }
}
