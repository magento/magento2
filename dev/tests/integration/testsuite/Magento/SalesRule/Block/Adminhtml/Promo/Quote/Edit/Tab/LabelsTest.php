<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class LabelsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(
            \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Labels::class,
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\View\Element\UiComponent\Argument\Interpreter\ConfigurableObject::class
            )->evaluate(
                [
                    'name' => 'block',
                    'value' => \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Labels::class
                ]
            )
        );
    }
}
