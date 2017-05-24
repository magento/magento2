<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

class QuoteConfigProductAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $plugin;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleResource;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ruleResource = $this->getMock(\Magento\SalesRule\Model\ResourceModel\Rule::class, [], [], '', false);

        $this->plugin = $objectManager->getObject(
            \Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes::class,
            [
                'ruleResource' => $this->ruleResource
            ]
        );
    }

    public function testAfterGetProductAttributes()
    {
        $subject = $this->getMock(\Magento\Quote\Model\Quote\Config::class, [], [], '', false);
        $attributeCode = 'code of the attribute';
        $expected = [0 => $attributeCode];

        $this->ruleResource->expects($this->once())
            ->method('getActiveAttributes')
            ->will(
                $this->returnValue(
                    [
                        ['attribute_code' => $attributeCode, 'enabled' => true],
                    ]
                )
            );

        $this->assertEquals($expected, $this->plugin->afterGetProductAttributes($subject, []));
    }
}
