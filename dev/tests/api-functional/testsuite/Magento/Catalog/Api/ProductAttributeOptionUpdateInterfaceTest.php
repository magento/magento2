<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Api;

use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class to test update Product Attribute Options
 */
class ProductAttributeOptionUpdateInterfaceTest extends WebapiAbstract
{
    private const SERVICE_NAME_UPDATE = 'catalogProductAttributeOptionUpdateV1';
    private const SERVICE_NAME = 'catalogProductAttributeOptionManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products/attributes';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test to update attribute option
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdate()
    {
        $testAttributeCode = 'select_attribute';
        $optionData = [
            AttributeOptionInterface::LABEL => 'Fixture Option Changed',
            AttributeOptionInterface::VALUE => 'option_value',
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => 'Store Label Changed',
                    AttributeOptionLabelInterface::STORE_ID => 1,
                ],
            ],
        ];

        $existOptionLabel = 'Fixture Option';
        $existAttributeOption = $this->getAttributeOption($testAttributeCode, $existOptionLabel, 'all');
        $optionId = $existAttributeOption['value'];

        $response = $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $optionId,
                'option' => $optionData,
            ],
            $optionId
        );

        $this->assertTrue($response);

        /* Check update option labels by stores */
        $expectedStoreLabels = [
            'all' => $optionData[AttributeOptionLabelInterface::LABEL],
            'default' => $optionData[AttributeOptionInterface::STORE_LABELS][0][AttributeOptionLabelInterface::LABEL],
        ];
        foreach ($expectedStoreLabels as $store => $label) {
            $this->assertNotNull($this->getAttributeOption($testAttributeCode, $label, $store));
        }
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website.id$'], 'store_group'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group.id$'], 'store1'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group.id$'], 'store2'),
        DataFixture(SelectAttributeFixture::class, ['default_frontend_label' => 'CustomAttr'], 'multi_store_attr'),
    ]
    public function testUpdateMultistorePreservingOtherStoreLabel()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
        $store1 = $this->fixtures->get('store1');
        $store2 = $this->fixtures->get('store2');
        $attributeCode = $this->fixtures->get('multi_store_attr')->getAttributeCode();
        $attributeLabel = 'Multi Store Option';
        $store1Label = 'Store 1 Label';
        $store2Label = 'Store 2 Label';
        $store1LabelUpdated = 'Store 1 Label Updated';

        // First, create an option with multiple store labels and add the option
        $initialOptionData = [
            AttributeOptionInterface::LABEL => $attributeLabel,
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => $store1Label,
                    AttributeOptionLabelInterface::STORE_ID => $store1->getId(),
                ],
                [
                    AttributeOptionLabelInterface::LABEL => $store2Label,
                    AttributeOptionLabelInterface::STORE_ID => $store2->getId(),
                ],
            ],
        ];
        $newOptionId = $this->webApiCallAttributeOptions(
            $attributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $attributeCode,
                'option' => $initialOptionData,
            ]
        );
        $this->assertNotNull($newOptionId, 'Option should be created successfully');

        // Now update only the first store label
        $updateOptionData = [
            AttributeOptionInterface::LABEL => $attributeLabel,
            AttributeOptionInterface::VALUE => $newOptionId,
            AttributeOptionInterface::STORE_LABELS => [
                [
                    AttributeOptionLabelInterface::LABEL => $store1LabelUpdated,
                    AttributeOptionLabelInterface::STORE_ID => $store1->getId(),
                ],
            ],
        ];
        $response = $this->webApiCallAttributeOptions(
            $attributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $attributeCode,
                'optionId' => $newOptionId,
                'option' => $updateOptionData,
            ],
            $newOptionId
        );
        $this->assertTrue($response, 'Update should be successful');

        $store1Options = $this->getAttributeOptions($attributeCode, $store1->getCode());
        $store2Options = $this->getAttributeOptions($attributeCode, $store2->getCode());

        // Find the option in store1 context
        $store1Option = null;
        foreach ($store1Options as $option) {
            if ($option['value'] === $newOptionId) {
                $store1Option = $option;
                break;
            }
        }
        // Find the option in store2 context
        $store2Option = null;
        foreach ($store2Options as $option) {
            if ($option['value'] === $newOptionId) {
                $store2Option = $option;
                break;
            }
        }

        // Verify that store1 label was updated
        $this->assertNotNull($store1Option, 'Option should exist in store1 context');
        $this->assertEquals(
            $store1LabelUpdated,
            $store1Option['label'],
            'Store1 label should be updated'
        );

        // Verify that store2 label was preserved
        $this->assertNotNull($store2Option, 'Option should exist in store2 context');
        $this->assertEquals(
            $store2Label,
            $store2Option['label'],
            'Store2 label should be preserved'
        );
    }

    /**
     * Test to update option with already exist exception
     *
     * Test to except case when the two options has a same label
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdateWithAlreadyExistsException()
    {
        $this->expectExceptionMessage("Admin store attribute option label '%1' already exists.");
        $testAttributeCode = 'select_attribute';

        $newOptionData = [
            AttributeOptionInterface::LABEL => 'New Option',
            AttributeOptionInterface::VALUE => 'new_option_value',
        ];
        $newOptionId = $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $testAttributeCode,
                'option' => $newOptionData,
            ]
        );

        $editOptionData = [
            AttributeOptionInterface::LABEL => 'Fixture Option',
            AttributeOptionInterface::VALUE => $newOptionId,
        ];
        $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $newOptionId,
                'option' => $editOptionData,
            ],
            $newOptionId
        );
    }

    /**
     * Test to update option with not exist exception
     *
     * @magentoApiDataFixture Magento/Catalog/Model/Product/Attribute/_files/select_attribute.php
     */
    public function testUpdateWithNotExistsException()
    {
        $this->expectExceptionMessage("The '%1' attribute doesn't include an option id '%2'.");
        $testAttributeCode = 'select_attribute';

        $newOptionData = [
            AttributeOptionInterface::LABEL => 'New Option',
            AttributeOptionInterface::VALUE => 'new_option_value'
        ];
        $newOptionId = (int)$this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_POST,
            'add',
            [
                'attributeCode' => $testAttributeCode,
                'option' => $newOptionData,
            ]
        );

        $newOptionId++;
        $editOptionData = [
            AttributeOptionInterface::LABEL => 'New Option Changed',
            AttributeOptionInterface::VALUE => $newOptionId
        ];
        $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_PUT,
            'update',
            [
                'attributeCode' => $testAttributeCode,
                'optionId' => $newOptionId,
                'option' => $editOptionData,
            ],
            $newOptionId
        );
    }

    /**
     * Perform Web API call to the system under test
     *
     * @param string $attributeCode
     * @param string $httpMethod
     * @param string $soapMethod
     * @param array $arguments
     * @param null $storeCode
     * @param null $optionId
     * @return array|bool|float|int|string
     */
    private function webApiCallAttributeOptions(
        string $attributeCode,
        string $httpMethod,
        string $soapMethod,
        array $arguments = [],
        $optionId = null,
        $storeCode = null
    ) {
        $resourcePath = self::RESOURCE_PATH . "/{$attributeCode}/options";
        if ($optionId) {
            $resourcePath .= '/' . $optionId;
        }
        $serviceName = $soapMethod === 'update' ? self::SERVICE_NAME_UPDATE : self::SERVICE_NAME;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => $httpMethod,
            ],
            'soap' => [
                'service' => $serviceName,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => $serviceName . $soapMethod,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $arguments, null, $storeCode);
    }

    /**
     * @param string $attributeCode
     * @param string $optionLabel
     * @param string|null $storeCode
     * @return array|null
     */
    private function getAttributeOption(
        string $attributeCode,
        string $optionLabel,
        ?string $storeCode = null
    ): ?array {
        $attributeOptions = $this->getAttributeOptions($attributeCode, $storeCode);
        $option = null;
        /** @var array $attributeOption */
        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption['label'] === $optionLabel) {
                $option = $attributeOption;
                break;
            }
        }

        return $option;
    }

    /**
     * @param string $testAttributeCode
     * @param string|null $storeCode
     * @return array|bool|float|int|string
     */
    private function getAttributeOptions(string $testAttributeCode, ?string $storeCode = null)
    {
        return $this->webApiCallAttributeOptions(
            $testAttributeCode,
            Request::HTTP_METHOD_GET,
            'getItems',
            ['attributeCode' => $testAttributeCode],
            null,
            $storeCode
        );
    }
}
