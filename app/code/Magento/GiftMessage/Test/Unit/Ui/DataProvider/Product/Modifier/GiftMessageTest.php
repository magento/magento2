<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GiftMessage\Ui\DataProvider\Product\Modifier\GiftMessage;

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
}
