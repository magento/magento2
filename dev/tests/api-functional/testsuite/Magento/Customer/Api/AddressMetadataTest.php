<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Api\Data\AddressInterface as Address;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Customer Address Metadata API test
 */
class AddressMetadataTest extends WebapiAbstract
{
    private const SERVICE_NAME = "customerAddressMetadataV1";
    private const SERVICE_VERSION = "V1";
    private const RESOURCE_PATH = "/V1/attributeMetadata/customerAddress";

    /**
     * @var Config $config
     */
    private $resourceConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = ObjectManager::getInstance();
        $this->resourceConfig = $objectManager->get(Config::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
    }

    /**
     * Test retrieval of attribute metadata for the address entity type.
     *
     * @param string $attributeCode The attribute code of the requested metadata.
     * @param array $expectedMetadata Expected entity metadata for the attribute code.
     * @dataProvider getAttributeMetadataDataProvider
     * @magentoDbIsolation disabled
     */
    public function testGetAttributeMetadata($attributeCode, $configOptions, $expectedMetadata)
    {
        $this->initConfig($configOptions);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/attribute/$attributeCode",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAttributeMetadata',
            ],
        ];

        $requestData = [
            'attributeCode' => $attributeCode,
        ];

        $attributeMetadata = $this->_webapiCall($serviceInfo, $requestData);
        $validationResult = $this->checkValidationRules($expectedMetadata, $attributeMetadata);
        list($expectedMetadata, $attributeMetadata) = $validationResult;
        $this->assertEquals($expectedMetadata, $attributeMetadata);
    }

    /**
     * Data provider for testGetAttributeMetadata.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributeMetadataDataProvider()
    {
        return [
            Address::POSTCODE => [
                Address::POSTCODE,
                [],
                [
                    AttributeMetadata::FRONTEND_INPUT => 'text',
                    AttributeMetadata::INPUT_FILTER => '',
                    AttributeMetadata::STORE_LABEL => 'Zip/Postal Code',
                    AttributeMetadata::MULTILINE_COUNT => 0,
                    AttributeMetadata::VALIDATION_RULES => [],
                    AttributeMetadata::VISIBLE => true,
                    AttributeMetadata::REQUIRED => false,
                    AttributeMetadata::DATA_MODEL => \Magento\Customer\Model\Attribute\Data\Postcode::class,
                    AttributeMetadata::OPTIONS => [],
                    AttributeMetadata::FRONTEND_CLASS => '',
                    AttributeMetadata::USER_DEFINED => false,
                    AttributeMetadata::SORT_ORDER => 110,
                    AttributeMetadata::FRONTEND_LABEL => 'Zip/Postal Code',
                    AttributeMetadata::NOTE => '',
                    AttributeMetadata::SYSTEM => true,
                    AttributeMetadata::BACKEND_TYPE => 'static',
                    AttributeMetadata::IS_USED_IN_GRID => true,
                    AttributeMetadata::IS_VISIBLE_IN_GRID => true,
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => true,
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => true,
                    AttributeMetadata::ATTRIBUTE_CODE => 'postcode',
                ],
            ],
            'prefix' => [
                'prefix',
                [
                    ['path' => 'customer/address/prefix_show', 'value' => 'opt'],
                    ['path' => 'customer/address/prefix_options', 'value' => 'prefA;prefB']
                ],
                [
                    AttributeMetadata::FRONTEND_INPUT => 'text',
                    AttributeMetadata::INPUT_FILTER => '',
                    AttributeMetadata::STORE_LABEL => 'Name Prefix',
                    AttributeMetadata::MULTILINE_COUNT => 0,
                    AttributeMetadata::VALIDATION_RULES => [],
                    AttributeMetadata::VISIBLE => false,
                    AttributeMetadata::REQUIRED => false,
                    AttributeMetadata::DATA_MODEL => '',
                    AttributeMetadata::OPTIONS => [
                        [
                            'label' => 'prefA',
                            'value' => 'prefA',
                        ],
                        [
                            'label' => 'prefB',
                            'value' => 'prefB',
                        ],
                    ],
                    AttributeMetadata::FRONTEND_CLASS => '',
                    AttributeMetadata::USER_DEFINED => false,
                    AttributeMetadata::SORT_ORDER => 10,
                    AttributeMetadata::FRONTEND_LABEL => 'Name Prefix',
                    AttributeMetadata::NOTE => '',
                    AttributeMetadata::SYSTEM => false,
                    AttributeMetadata::BACKEND_TYPE => 'static',
                    AttributeMetadata::IS_USED_IN_GRID => false,
                    AttributeMetadata::IS_VISIBLE_IN_GRID => false,
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => false,
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => false,
                    AttributeMetadata::ATTRIBUTE_CODE => 'prefix',
                ],
            ],
            'suffix' => [
                'suffix',
                [
                    ['path' => 'customer/address/suffix_show', 'value' => 'opt'],
                    ['path' => 'customer/address/suffix_options', 'value' => 'suffA;suffB']
                ],
                [
                    AttributeMetadata::FRONTEND_INPUT => 'text',
                    AttributeMetadata::INPUT_FILTER => '',
                    AttributeMetadata::STORE_LABEL => 'Name Suffix',
                    AttributeMetadata::MULTILINE_COUNT => 0,
                    AttributeMetadata::VALIDATION_RULES => [],
                    AttributeMetadata::VISIBLE => false,
                    AttributeMetadata::REQUIRED => false,
                    AttributeMetadata::DATA_MODEL => '',
                    AttributeMetadata::OPTIONS => [
                        [
                            'label' => 'suffA',
                            'value' => 'suffA',
                        ],
                        [
                            'label' => 'suffB',
                            'value' => 'suffB',
                        ],
                    ],
                    AttributeMetadata::FRONTEND_CLASS => '',
                    AttributeMetadata::USER_DEFINED => false,
                    AttributeMetadata::SORT_ORDER => 50,
                    AttributeMetadata::FRONTEND_LABEL => 'Name Suffix',
                    AttributeMetadata::NOTE => '',
                    AttributeMetadata::SYSTEM => false,
                    AttributeMetadata::BACKEND_TYPE => 'static',
                    AttributeMetadata::IS_USED_IN_GRID => false,
                    AttributeMetadata::IS_VISIBLE_IN_GRID => false,
                    AttributeMetadata::IS_FILTERABLE_IN_GRID => false,
                    AttributeMetadata::IS_SEARCHABLE_IN_GRID => false,
                    AttributeMetadata::ATTRIBUTE_CODE => 'suffix',
                ],
            ],
        ];
    }

    /**
     * Test retrieval of all address attribute metadata.
     */
    public function testGetAllAttributesMetadata()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAllAttributesMetadata',
            ],
        ];

        $attributeMetadata = $this->_webApiCall($serviceInfo);
        $this->assertCount(19, $attributeMetadata);
        $postcode = $this->getAttributeMetadataDataProvider()[Address::POSTCODE][2];
        $validationResult = $this->checkMultipleAttributesValidationRules($postcode, $attributeMetadata);
        list($postcode, $attributeMetadata) = $validationResult;
        $this->assertContainsEquals($postcode, $attributeMetadata);
    }

    /**
     * Test retrieval of custom address attribute metadata.
     *
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testGetCustomAttributesMetadata()
    {
        $customAttributeCode = 'custom_attribute1';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/custom',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCustomAttributesMetadata',
            ],
        ];

        $requestData = ['attribute_code' => $customAttributeCode];
        $attributeMetadata = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertCount(2, $attributeMetadata);
        $this->assertEquals($customAttributeCode, $attributeMetadata[0]['attribute_code']);
    }

    /**
     * Test retrieval of attributes
     *
     * @param string $formCode Form code
     * @param array $expectedMetadata The expected attribute metadata
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($formCode, $expectedMetadata)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/form/$formCode",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAttributes',
            ],
        ];

        $requestData = [
            'formCode' => $formCode,
        ];

        $attributeMetadataList = $this->_webApiCall($serviceInfo, $requestData);
        foreach ($attributeMetadataList as $attributeMetadata) {
            if (isset($attributeMetadata['attribute_code'])
                && $attributeMetadata['attribute_code'] == $expectedMetadata['attribute_code']
            ) {
                $validationResult = $this->checkValidationRules($expectedMetadata, $attributeMetadata);
                list($expectedMetadata, $attributeMetadata) = $validationResult;
                $this->assertEquals($expectedMetadata, $attributeMetadata);
                break;
            }
        }
    }

    /**
     * Data provider for testGetAttributes.
     *
     * @return array
     */
    public function getAttributesDataProvider()
    {
        $attributeMetadata = $this->getAttributeMetadataDataProvider();
        return [
            [
                'customer_address_edit',
                $attributeMetadata[Address::POSTCODE][2],
            ]
        ];
    }

    /**
     * Checks that expected and actual attribute metadata validation rules are equal
     * and removes the validation rules entry from expected and actual attribute metadata
     *
     * @param array $expectedResult
     * @param array $actualResult
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function checkValidationRules($expectedResult, $actualResult)
    {
        $expectedRules = [];
        $actualRules = [];

        if (isset($expectedResult[AttributeMetadata::VALIDATION_RULES])) {
            $expectedRules = $expectedResult[AttributeMetadata::VALIDATION_RULES];
            unset($expectedResult[AttributeMetadata::VALIDATION_RULES]);
        }
        if (isset($actualResult[AttributeMetadata::VALIDATION_RULES])) {
            $actualRules = $actualResult[AttributeMetadata::VALIDATION_RULES];
            unset($actualResult[AttributeMetadata::VALIDATION_RULES]);
        }

        if (is_array($expectedRules) && is_array($actualRules)) {
            foreach ($expectedRules as $expectedRule) {
                if (isset($expectedRule['name']) && isset($expectedRule['value'])) {
                    $found = false;
                    foreach ($actualRules as $actualRule) {
                        if (isset($actualRule['name']) && isset($actualRule['value'])) {
                            if ($expectedRule['name'] == $actualRule['name']
                                && $expectedRule['value'] == $actualRule['value']
                            ) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    $this->assertTrue($found);
                }
            }
        }
        return [$expectedResult, $actualResult];
    }
    //phpcs:enable

    /**
     * Check specific attribute validation rules in set of multiple attributes
     *
     * @param array $expectedResult Set of expected attribute metadata
     * @param array $actualResultSet Set of actual attribute metadata
     * @return array
     */
    public function checkMultipleAttributesValidationRules($expectedResult, $actualResultSet)
    {
        if (is_array($expectedResult) && is_array($actualResultSet)) {
            if (isset($expectedResult[AttributeMetadata::ATTRIBUTE_CODE])) {
                foreach ($actualResultSet as $actualAttributeKey => $actualAttribute) {
                    if (isset($actualAttribute[AttributeMetadata::ATTRIBUTE_CODE])
                        && $expectedResult[AttributeMetadata::ATTRIBUTE_CODE]
                        == $actualAttribute[AttributeMetadata::ATTRIBUTE_CODE]
                    ) {
                        $this->checkValidationRules($expectedResult, $actualAttribute);
                        unset($actualResultSet[$actualAttributeKey][AttributeMetadata::VALIDATION_RULES]);
                    }
                }
                unset($expectedResult[AttributeMetadata::VALIDATION_RULES]);
            }
        }
        return [$expectedResult, $actualResultSet];
    }

    /**
     * Set core config data.
     *
     * @param $configOptions
     */
    private function initConfig(array $configOptions): void
    {
        if ($configOptions) {
            foreach ($configOptions as $option) {
                $this->resourceConfig->saveConfig($option['path'], $option['value']);
            }
        }
        $this->reinitConfig->reinit();
    }
}
