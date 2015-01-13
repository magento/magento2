<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * @magentoAppArea adminhtml
 */
class NewActionTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @TODO: Remove this test when corresponding functional tests become mandatory:
     *
     * Magento\Catalog\Test\TestCase\Product\CreateSimpleProductEntityTest::testCreate() variations: #5, #6.
     * Magento\Catalog\Test\TestCase\Product\CreateVirtualProductEntityTest::testCreate variations: #4, #5.
     * Magento\Catalog\Test\TestCase\Product\UpdateVirtualProductEntityTest::test variations: #6, #8, #10, #11.
     */
    public function testCustomerGroupArePresentInGroupPriceTemplate()
    {
        $this->dispatch('backend/catalog/product/new/set/'
            . \Magento\Catalog\Api\Data\ProductAttributeInterface::DEFAULT_ATTRIBUTE_SET_ID
            . '/type/' . \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        );
        $lines = explode(PHP_EOL, $this->getResponse()->getBody());
        foreach ($lines as $index => $line) {
            if ($line && strpos($line, 'name="product[group_price][{{index}}][cust_group]"') !== false) {
                break;
            }
        }
        $this->assertContains('name="product[group_price][{{index}}][cust_group]"', $lines[$index]);
        $this->assertContains('<option value="0">NOT LOGGED IN</option>', $lines[$index + 1]);
        $this->assertContains('<option value="1">General</option>', $lines[$index + 2]);
        $this->assertContains('<option value="2">Wholesale</option>', $lines[$index + 3]);
        $this->assertContains('<option value="3">Retailer</option>', $lines[$index + 4]);
    }
}
