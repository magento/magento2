<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductCustomOptionsTest extends WebapiAbstract
{
    public const RESOURCE_PATH = '/V1/products';
    public const CONFIG_RESOURCE_PATH = '/V1/configurable-products';
    public const CONFIG_SERVICE_NAME = 'configurableProductLinkManagementV1';
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'catalogProductCustomOptionRepositoryV1';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomOptionsModifier
     */
    private $customOptionModifier;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $option;

    /** @var Registry */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->customOptionModifier = $this->objectManager->get(CustomOptionsModifier::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->option = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearData();
    }

    /**
     * Test to verify customizable options honour `use default value` checkbox check
     * @dataProvider optionDataProvider
     * @param array $optionData
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        Config('web/url/use_store', 1),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store2'),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
    ]
    public function testModifyData(array $optionData): void
    {
        $childProduct = $this->fixtures->get('p2');
        $product = $this->fixtures->get('cp1');
        $store2 = $this->fixtures->get('store2');

        $productSku = $product->getSku();
        $childSku = $childProduct->getSku();
        $storeCode = $store2->getCode();
        $optionDataPost = $optionData;
        $optionDataPost['product_sku'] = $productSku;
        $this->addCustomizableOptions($optionDataPost);
        $this->assertTrue(
            $this->removeChild($productSku, $childSku, $storeCode),
            'Unable to remove child product.'
        );
        $this->assertTrue(
            $this->addChild($productSku, $childSku, $storeCode),
            'Unable to link child product.'
        );

        $response = $this->productDataSource($product, $store2);
        $productId = $product->getId();
        $this->assertTrue($response[$productId]['product']['options'][0]['is_use_default']);
        if (in_array($optionData['type'], ['drop_down', 'radio', 'checkbox', 'multiple'])) {
            $this->assertTrue($response[$productId]['product']['options'][0]['values'][0]['is_use_default']);
        }
        //delete custom options for db cleanup
        $this->deleteCustomizableOption($productSku, (int)$response[$productId]['product']['options'][0]['option_id']);
    }

    /**
     * Perform add child product Api call
     *
     * @param string $productSku
     * @param string $childSku
     * @param string $storeCode
     * @return bool
     */
    private function addChild(string $productSku, string $childSku, string $storeCode): bool
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::CONFIG_RESOURCE_PATH . '/' . $productSku . '/child',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::CONFIG_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::CONFIG_SERVICE_NAME . 'AddChild'
            ]
        ];

        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'childSku' => $childSku], null, $storeCode);
    }

    /**
     * Unassign child product
     *
     * @param string $productSku
     * @param string $childSku
     * @param string $storeCode
     * @return bool
     */
    private function removeChild(string $productSku, string $childSku, string $storeCode): bool
    {
        $resourcePath = self::CONFIG_RESOURCE_PATH . '/%s/children/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $childSku),
                'httpMethod' => Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::CONFIG_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::CONFIG_SERVICE_NAME . 'RemoveChild'
            ]
        ];
        $requestData = ['sku' => $productSku, 'childSku' => $childSku];
        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    /**
     * Fetch product data with customizable options
     *
     * @param $configurableProduct
     * @param $store
     * @return array
     * @throws NoSuchEntityException
     */
    private function productDataSource($configurableProduct, $store): array
    {
        $this->clearData();
        $productId = $configurableProduct->getData('entity_id');
        $this->request->setParams([
            'id' => $productId,
            'store' => $store->getId(),
        ]);
        $product = $this->productRepository->getById($productId, true, $store->getId());
        $this->registry->register('current_product', $product);

        return $this->customOptionModifier->modifyData(
            [
                $productId => [
                    'product' => $product->getData()
                ]
            ]
        );
    }

    /**
     * Product's customizable options data provider
     *
     * @return array
     */
    public static function optionDataProvider(): array
    {
        $fixtureOptions = [];
        $fixture = include '_files/product_options.php';
        foreach ($fixture as $item) {
            $fixtureOptions[$item['type']] = [
                'optionData' => $item,
            ];
        }

        return $fixtureOptions;
    }

    /**
     * Add customizable options to catalog > product
     *
     * @param $option
     * @return void
     */
    private function addCustomizableOptions($option): void
    {
        /** @var ProductCustomOptionInterfaceFactory $customOptionFactory */
        $customOptionFactory = $this->objectManager->create(ProductCustomOptionInterfaceFactory::class);
        $customOption = $customOptionFactory->create(['data' => $option]);
        $this->option->save($customOption);
    }

    /**
     * Delete customizable option by product SKU and option ID
     *
     * @param string $sku
     * @param int $optionId
     * @return bool
     */
    private function deleteCustomizableOption(string $sku, int $optionId): bool
    {
        return $this->option->deleteByIdentifier($sku, $optionId);
    }

    /**
     * Remove request params
     *
     * @return void
     */
    private function clearData(): void
    {
        $this->request->setParams([]);
        $this->registry->unregister('current_product');
    }
}
