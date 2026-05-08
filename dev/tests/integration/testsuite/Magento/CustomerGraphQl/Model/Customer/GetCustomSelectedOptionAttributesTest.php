<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Eav\Test\Fixture\AttributeOption;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => 'select_attr',
            'frontend_input' => 'select'
        ],
        'attribute'
    ),
    DataFixture(
        AttributeOption::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$attribute.attribute_code$',
            'label' => 'option1 label',
            'sort_order' => 10
        ],
        'option1'
    ),
    DataFixture(
        AttributeOption::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$attribute.attribute_code$',
            'label' => 'option2 label',
            'sort_order' => 20
        ],
        'option2'
    )
]
class GetCustomSelectedOptionAttributesTest extends TestCase
{
    /**
     * @var GetCustomSelectedOptionAttributes
     */
    private $getCustomSelectedOptionAttributes;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->getCustomSelectedOptionAttributes = Bootstrap::getObjectManager()
            ->create(GetCustomSelectedOptionAttributes::class);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCustomerCustomOptionAttributeWithIntegerValue(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');
        /** @var AttributeOptionInterface $option1 */
        $option1 = DataFixtureStorageManager::getStorage()->get('option1');
        $customAttribute['attribute_code'] = $attribute->getAttributeCode();
        $customAttribute['value'] = (int)$option1->getValue();
        $result = $this->getCustomSelectedOptionAttributes->execute('customer', $customAttribute);
        unset($result[0]['uid']);
        $this->assertEquals(
            [
                [
                    'value' => $option1->getValue(),
                    'label' => $option1->getLabel()
                ]
            ],
            $result
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCustomerCustomOptionAttributeWithStringValue(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        /** @var AttributeOptionInterface $option1 */
        $option1 = DataFixtureStorageManager::getStorage()->get('option1');

        /** @var AttributeOptionInterface $option2 */
        $option2 = DataFixtureStorageManager::getStorage()->get('option2');

        $customAttribute['attribute_code'] = $attribute->getAttributeCode();

        $customAttribute['value'] = $option1->getValue().",".$option2->getValue();

        $result = $this->getCustomSelectedOptionAttributes->execute('customer', $customAttribute);

        foreach ($result as &$tmp) {
            unset($tmp['uid']);
        }
        $this->assertEquals(
            [
                [
                    'value' => $option1->getValue(),
                    'label' => $option1->getLabel()
                ],
                [
                    'value' => $option2->getValue(),
                    'label' => $option2->getLabel()
                ]
            ],
            $result
        );
    }
}
