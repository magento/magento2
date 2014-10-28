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

namespace Magento\Sales\Block\Adminhtml\Report\Filter\Form;

/**
 * @magentoAppArea adminhtml
 */
class CouponTest extends \PHPUnit_Framework_TestCase
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
            ->get('Magento\Framework\View\LayoutInterface');
    }

    /**
     * @covers \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon::_afterToHtml
     */
    public function testAfterToHtml()
    {
        /** @var $block \Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon */
        $block = $this->_layout->createBlock('Magento\Sales\Block\Adminhtml\Report\Filter\Form\Coupon');
        $block->setFilterData(new \Magento\Framework\Object());
        $html = $block->toHtml();

        $expectedStrings = array(
            'FormElementDependenceController',
            'sales_report_rules_list',
            'sales_report_price_rule_type'
        );
        foreach ($expectedStrings as $expectedString) {
            $this->assertContains($expectedString, $html);
        }
    }
}
