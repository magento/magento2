<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

class QuoteConfigProductAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $plugin;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleResource;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ruleResource = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule::class);

        $this->plugin = $objectManager->getObject(
            \Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes::class,
            [
                'ruleResource' => $this->ruleResource
            ]
        );
    }

    public function testAfterGetProductAttributes()
    {
        $subject = $this->createMock(\Magento\Quote\Model\Quote\Config::class);
        $attributeCode = 'code of the attribute';
        $expected = [0 => $attributeCode];

        $this->ruleResource->expects($this->once())
            ->method('getActiveAttributes')
            ->willReturn(
                
                    [
                        ['attribute_code' => $attributeCode, 'enabled' => true],
                    ]
                
            );

        $this->assertEquals($expected, $this->plugin->afterGetProductAttributes($subject, []));
    }
}
