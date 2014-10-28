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
namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

class CustomergroupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup'
        );
    }

    public function test_toHtml()
    {
        $this->_block->setClass('customer_group_select');
        $this->_block->setId('123');
        $this->_block->setTitle('Customer Group');
        $this->_block->setInputName('groups[item_options]');
        $expectedResult = '<select name="groups[item_options]" id="123" class="customer_group_select" '
            . 'title="Customer Group" ><option value="32000" >ALL GROUPS</option><option value="0" >NOT LOGGED IN'
            . '</option><option value="1" >General</option><option value="2" >Wholesale</option><option value="3" >'
            . 'Retailer</option></select>';
        $this->assertEquals($expectedResult, $this->_block->_toHtml());
    }
}
