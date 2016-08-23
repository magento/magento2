<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GiftMessage\Ui\DataProvider\Product\Modifier\GiftMessage;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * Class GiftMessageTest
 *
 * @method GiftMessage getModel
 */
class GiftMessageTest extends AbstractModifierTest
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        parent::setUp();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(GiftMessage::class, [
            'locator' => $this->locatorMock,
            'scopeConfig' => $this->scopeConfigMock,
        ]);
    }

    public function testModifyData()
    {
        $this->assertNotEmpty($this->getModel()->modifyData(
            [
                1 => [
                    GiftMessage::DATA_SOURCE_DEFAULT => [
                        GiftMessage::FIELD_MESSAGE_AVAILABLE => true,
                    ],
                ],
            ]
        ));
    }

    public function testModifyMeta()
    {
        $this->assertNotEmpty($this->getModel()->modifyMeta(
            [
                'test_group_code' => [
                    'children' => [
                        GiftMessage::FIELD_MESSAGE_AVAILABLE => [
                            'label' => __('Test label'),
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ]
        ));
    }

    public function testModifyDataUsesConfigurationValuesWhenProductDoesNotContainValidValue()
    {
        $productId = 1;
        $this->productMock->expects($this->any())->method('getId')->willReturn($productId);

        $configValue = 1;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $data = [$productId => [
            GiftMessage::DATA_SOURCE_DEFAULT => [
                GiftMessage::FIELD_MESSAGE_AVAILABLE => Boolean::VALUE_USE_CONFIG,
            ],
        ]];
        $expectedResult = [$productId => [
            GiftMessage::DATA_SOURCE_DEFAULT => [
                GiftMessage::FIELD_MESSAGE_AVAILABLE => $configValue,
                'use_config_gift_message_available' => 1
            ],
        ]];

        $this->assertEquals($expectedResult, $this->getModel()->modifyData($data));
    }

    public function testModifyDataUsesConfigurationValuesForNewProduct()
    {
        $productId = null;
        $configValue = 1;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        $expectedResult = [$productId => [
            GiftMessage::DATA_SOURCE_DEFAULT => [
                GiftMessage::FIELD_MESSAGE_AVAILABLE => $configValue,
                'use_config_gift_message_available' => 1
            ],
        ]];

        $this->assertEquals($expectedResult, $this->getModel()->modifyData([]));
    }
}
