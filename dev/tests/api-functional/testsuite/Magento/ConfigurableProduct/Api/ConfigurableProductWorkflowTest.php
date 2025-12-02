<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Authorization\Test\Fixture\Role;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Test\Fixture\User;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Complete workflow test for configurable product creation via REST API
 */
class ConfigurableProductWorkflowTest extends WebapiAbstract
{
    private const PRODUCTS_RESOURCE_PATH = '/V1/products';
    private const CONFIGURABLE_PRODUCT_SKU = 'CONFIGURABLE_BY_API';
    private const CHILD_PRODUCT_SKU = 'SIMPLE_CHILD_RED';

    /**
     * @var string
     */
    private string $adminToken;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var AdminTokenServiceInterface
     */
    private AdminTokenServiceInterface $adminTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        $this->fixtures = Bootstrap::getObjectManager()
            ->get(DataFixtureStorageManager::class)
            ->getStorage();
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
    }

    /**
     * Test complete configurable product creation workflow via REST API
     *
     * Covers:
     * - Admin authentication
     * - Configurable product creation with color attribute
     * - Child product creation and linking
     * - Display out of stock products configuration
     *
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(
            Role::class,
            ['role_name' => 'Test Admin Role', 'resources' => ['Magento_Backend::all']],
            'admin_role'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'test_admin_user',
                'firstname' => 'Test',
                'lastname' => 'Admin',
                'email' => 'testadmin@example.com',
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                'role_id' => '$admin_role.role_id$'
            ],
            'admin_user'
        ),
        DataFixture(
            AttributeFixture::class,
            [
                'attribute_code' => 'test_configurable',
                'frontend_input' => 'select',
                'frontend_label' => 'Test Configurable',
                'is_configurable' => true,
                'options' => [
                    ['label' => 'Option 1', 'sort_order' => 10],
                    ['label' => 'Option 2', 'sort_order' => 20],
                ],
            ],
            'configurable_attribute'
        ),
        Config('cataloginventory/options/show_out_of_stock', '1', 'default')
    ]
    public function testCompleteConfigurableProductWorkflow(): void
    {
        // Get the configurable attribute created by fixture
        $colorAttribute = $this->fixtures->get('configurable_attribute');

        // Get the option values from the attribute
        $colorOptions = $colorAttribute->getOptions();
        array_shift($colorOptions); // Remove the first empty option
        $option1 = $colorOptions[0]; // Option 1
        $option2 = $colorOptions[1]; // Option 2

        // Obtain admin access token
        $this->adminToken = $this->getAdminAccessToken();
        $this->assertNotEmpty($this->adminToken, 'Admin access token should not be empty');

        // Create configurable product
        $configurableProductData = $this->createConfigurableProduct();
        $this->assertEquals(self::CONFIGURABLE_PRODUCT_SKU, $configurableProductData['sku']);
        $this->assertEquals(Configurable::TYPE_CODE, $configurableProductData['type_id']);
        $this->assertEquals(Status::STATUS_ENABLED, $configurableProductData['status']);
        $this->assertEquals(Visibility::VISIBILITY_BOTH, $configurableProductData['visibility']);

        // Add color attribute options to configurable product
        $optionResult = $this->addConfigurableProductOption(
            self::CONFIGURABLE_PRODUCT_SKU,
            (int)$colorAttribute->getAttributeId(),
            [
                ['value_index' => $option1->getValue()],
                ['value_index' => $option2->getValue()]
            ]
        );
        $this->assertNotEmpty($optionResult, 'Configurable product option should be created successfully');

        // Create child product with first option
        $childProductData = $this->createChildProduct(
            self::CHILD_PRODUCT_SKU,
            $colorAttribute->getAttributeCode(),
            $option1->getValue()
        );
        $this->assertEquals(self::CHILD_PRODUCT_SKU, $childProductData['sku']);
        $this->assertEquals('simple', $childProductData['type_id']);

        // Link child product to configurable product
        $linkResult = $this->linkChildToConfigurableProduct(
            self::CONFIGURABLE_PRODUCT_SKU,
            self::CHILD_PRODUCT_SKU
        );
        $this->assertTrue($linkResult, 'Child product should be linked successfully');

        // Verify the complete setup
        $this->verifyConfigurableProductSetup();

        // Cleanup
        $this->cleanupProducts();
    }

    /**
     * Test linking non-existent child to configurable product
     *
     * @return void
     */
    #[
        DataFixture(
            Role::class,
            [
                'role_name' => 'Test Admin Role',
                'resources' => ['Magento_Backend::all']
            ],
            'admin_role'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'test_admin_user',
                'firstname' => 'Test',
                'lastname' => 'Admin',
                'email' => 'testadmin@example.com',
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
                'role_id' => '$admin_role.role_id$'
            ],
            'admin_user'
        )
    ]
    public function testLinkNonExistentChild(): void
    {
        $this->adminToken = $this->getAdminAccessToken();

        // Create configurable product first
        $this->createConfigurableProduct();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        $this->linkChildToConfigurableProduct(
            self::CONFIGURABLE_PRODUCT_SKU,
            'non_existent_sku'
        );
    }

    /**
     * Get admin access token using fixture-created user
     *
     * @return string
     */
    private function getAdminAccessToken(): string
    {
        $adminUser = $this->fixtures->get('admin_user');

        return $this->adminTokens->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
    }

    /**
     * Create configurable product via REST API
     *
     * @return array
     */
    private function createConfigurableProduct(): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRODUCTS_RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->adminToken,
            ],
        ];

        $productData = [
            'product' => [
                'sku' => self::CONFIGURABLE_PRODUCT_SKU,
                'name' => 'Configurable Product Created by API',
                'attribute_set_id' => 4, // Default attribute set
                'type_id' => Configurable::TYPE_CODE,
                'price' => 99.00,
                'status' => Status::STATUS_ENABLED,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'weight' => 1.0,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'description',
                        'value' => 'Test configurable product created via REST API'
                    ],
                    [
                        'attribute_code' => 'meta_description',
                        'value' => 'Meta description for configurable product'
                    ],
                    [
                        'attribute_code' => 'tax_class_id',
                        'value' => 2 // Taxable Goods
                    ]
                ]
            ]
        ];

        return $this->_webApiCall($serviceInfo, $productData);
    }

    /**
     * Add configurable product option
     *
     * @param string $productSku
     * @param int $attributeId
     * @param array $attributeValues
     * @return array|bool|float|int|string
     */
    private function addConfigurableProductOption(
        string $productSku,
        int $attributeId,
        array $attributeValues
    ) {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf('/V1/configurable-products/%s/options', $productSku),
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->adminToken,
            ],
        ];

        $requestData = [
            'sku' => $productSku,
            'option' => [
                'attribute_id' => $attributeId,
                'label' => 'Color',
                'position' => 0,
                'is_use_default' => true,
                'values' => $attributeValues
            ]
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Create child (simple) product
     *
     * @param string $sku
     * @param string $colorAttributeCode
     * @param string $colorValue
     * @return array
     */
    private function createChildProduct(string $sku, string $colorAttributeCode, string $colorValue): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRODUCTS_RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->adminToken,
            ],
        ];

        $productData = [
            'product' => [
                'sku' => $sku,
                'name' => 'Simple Product - Red Color',
                'attribute_set_id' => 4,
                'type_id' => 'simple',
                'price' => 25.99,
                'status' => 1,
                'visibility' => 1, // Not Visible Individually (child products)
                'weight' => 0.5,
                'custom_attributes' => [
                    [
                        'attribute_code' => $colorAttributeCode,
                        'value' => $colorValue
                    ],
                    [
                        'attribute_code' => 'description',
                        'value' => 'Child product with red color'
                    ],
                    [
                        'attribute_code' => 'tax_class_id',
                        'value' => 2
                    ]
                ]
            ]
        ];

        return $this->_webApiCall($serviceInfo, $productData);
    }

    /**
     * Link child product to configurable product
     *
     * @param string $configurableSku
     * @param string $childSku
     * @return bool
     */
    private function linkChildToConfigurableProduct(string $configurableSku, string $childSku): bool
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf('/V1/configurable-products/%s/child', $configurableSku),
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->adminToken,
            ],
        ];

        $requestData = [
            'childSku' => $childSku
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Verify the complete configurable product setup
     *
     * @return void
     */
    private function verifyConfigurableProductSetup(): void
    {
        // Get configurable product to verify options and links
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRODUCTS_RESOURCE_PATH . '/' . self::CONFIGURABLE_PRODUCT_SKU,
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $this->adminToken,
            ],
        ];

        $product = $this->_webApiCall($serviceInfo);

        // Verify configurable product has extension attributes
        $this->assertArrayHasKey('extension_attributes', $product);

        $extensionAttributes = $product['extension_attributes'];

        // Verify configurable product options exist
        $this->assertArrayHasKey('configurable_product_options', $extensionAttributes);
        $this->assertNotEmpty($extensionAttributes['configurable_product_options']);

        $options = $extensionAttributes['configurable_product_options'];
        $this->assertCount(1, $options); // Should have 1 configurable option

        $configurableOption = $options[0];
        $this->assertEquals('Test Configurable', $configurableOption['label']);
        $this->assertNotEmpty($configurableOption['values']); // Should have option values

        // Verify configurable product links exist
        $this->assertArrayHasKey('configurable_product_links', $extensionAttributes);
        $this->assertNotEmpty($extensionAttributes['configurable_product_links']);

        $links = $extensionAttributes['configurable_product_links'];
        $this->assertIsArray($links, 'Configurable product links should be an array');
        $this->assertCount(1, $links, 'Should have exactly 1 linked child product');
    }

    /**
     * Clean up created products
     *
     * @return void
     */
    private function cleanupProducts(): void
    {
        $productsToDelete = [self::CONFIGURABLE_PRODUCT_SKU, self::CHILD_PRODUCT_SKU];

        foreach ($productsToDelete as $sku) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::PRODUCTS_RESOURCE_PATH . '/' . $sku,
                    'httpMethod' => Request::HTTP_METHOD_DELETE,
                    'token' => $this->adminToken,
                ],
            ];

            $this->_webApiCall($serviceInfo);
        }
    }
}
