<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Report\Filter\Form;

/**
 * @magentoAppArea adminhtml
 */
class CouponTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    protected function setUp()
    {
        parent::setUp();
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\View\LayoutInterface::class);
    }

    /**
     * @covers \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon::_afterToHtml
     */
    public function testAfterToHtml()
    {
        /** @var $block \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon */
        $block = $this->_layout->createBlock(\Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon::class);
        $block->setFilterData(new \Magento\Framework\DataObject());
        $html = $block->toHtml();

        $expectedStrings = [
            'FormElementDependenceController',
            'sales_report_rules_list',
            'sales_report_price_rule_type',
        ];
        foreach ($expectedStrings as $expectedString) {
            $this->assertContains($expectedString, $html);
        }
    }
}
