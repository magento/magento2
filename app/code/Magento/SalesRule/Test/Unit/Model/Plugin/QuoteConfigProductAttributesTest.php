<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Config;
use Magento\SalesRule\Model\Plugin\QuoteConfigProductAttributes;
use Magento\SalesRule\Model\ResourceModel\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteConfigProductAttributesTest extends TestCase
{
    /**
     * @var QuoteConfigProductAttributes|MockObject
     */
    protected $plugin;

    /**
     * @var Rule|MockObject
     */
    protected $ruleResource;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->ruleResource = $this->createMock(Rule::class);

        $this->plugin = $objectManager->getObject(
            QuoteConfigProductAttributes::class,
            [
                'ruleResource' => $this->ruleResource
            ]
        );
    }

    public function testAfterGetProductAttributes()
    {
        $subject = $this->createMock(Config::class);
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
