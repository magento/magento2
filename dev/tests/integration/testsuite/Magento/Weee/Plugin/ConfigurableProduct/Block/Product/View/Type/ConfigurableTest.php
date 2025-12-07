<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Attribute as WeeeAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for WEEE plugin on Configurable Product JSON config
 *
 * @appIsolation enabled
 * @dbIsolation enabled
 * @magentoAppArea frontend
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var ConfigurableBlock
     */
    private $configurableBlock;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MutableScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->configurableBlock = $objectManager->get(ConfigurableBlock::class);
        $this->jsonDecoder = $objectManager->get(DecoderInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->scopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->registry = $objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');
    }

    /**
     * Test that WEEE data is added to configurable product JSON config
     *
     * @return void
     */
    #[
        DataFixture(
            WeeeAttributeFixture::class,
            [
                'attribute_code' => 'test_fpt_attr',
                'frontend_input' => 'weee',
                'frontend_label' => 'Test FPT'
            ],
            'weee_attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'weight' => 1,
                'test_fpt_attr' => [
                    ['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.50, 'delete' => '']
                ]
            ],
            'simple1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 150,
                'weight' => 1,
                'test_fpt_attr' => [
                    ['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 15.75, 'delete' => '']
                ]
            ],
            'simple2'
        ),
        DataFixture(
            AttributeFixture::class,
            ['options' => [['label' => 'Option 1'], ['label' => 'Option 2']]],
            'attr'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable-weee-test',
                '_options' => ['$attr$'],
                '_links' => ['$simple1$', '$simple2$']
            ],
            'configurable'
        )
    ]
    public function testWeeeDataAddedToJsonConfig(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableProduct = $fixtures->get('configurable');
        $simple1 = $fixtures->get('simple1');
        $simple2 = $fixtures->get('simple2');

        // Enable WEEE
        $this->scopeConfig->setValue(
            'tax/weee/enable',
            1,
            ScopeInterface::SCOPE_STORE
        );

        // Set current product in registry for the block
        $configurableProduct = $this->productRepository->getById($configurableProduct->getId());
        $this->registry->register('product', $configurableProduct);
        $this->registry->register('current_product', $configurableProduct);

        // Get JSON config
        $jsonConfig = $this->configurableBlock->getJsonConfig();
        $config = $this->jsonDecoder->decode($jsonConfig);

        // Assert WEEE data is present in optionPrices
        $this->assertArrayHasKey('optionPrices', $config);
        $this->assertArrayHasKey((string)$simple1->getId(), $config['optionPrices']);
        $this->assertArrayHasKey((string)$simple2->getId(), $config['optionPrices']);

        // Assert WEEE data for simple1
        $simple1Price = $config['optionPrices'][(string)$simple1->getId()]['finalPrice'];
        $this->assertArrayHasKey('weeeAmount', $simple1Price);
        $this->assertArrayHasKey('weeeAttributes', $simple1Price);
        $this->assertArrayHasKey('amountWithoutWeee', $simple1Price);
        $this->assertArrayHasKey('formattedWithoutWeee', $simple1Price);
        $this->assertArrayHasKey('formattedWithWeee', $simple1Price);

        $this->assertEquals(10.50, $simple1Price['weeeAmount']);
        $this->assertNotEmpty($simple1Price['weeeAttributes']);
        $this->assertIsArray($simple1Price['weeeAttributes']);

        // Assert WEEE attribute structure
        $weeeAttr1 = $simple1Price['weeeAttributes'][0];
        $this->assertArrayHasKey('name', $weeeAttr1);
        $this->assertArrayHasKey('amount', $weeeAttr1);
        $this->assertArrayHasKey('formatted', $weeeAttr1);
        $this->assertEquals(10.50, $weeeAttr1['amount']);

        // Assert WEEE data for simple2
        $simple2Price = $config['optionPrices'][(string)$simple2->getId()]['finalPrice'];
        $this->assertArrayHasKey('weeeAmount', $simple2Price);
        $this->assertEquals(15.75, $simple2Price['weeeAmount']);

        // Assert price calculations
        $this->assertEquals(
            $simple1Price['amount'] - $simple1Price['weeeAmount'],
            $simple1Price['amountWithoutWeee']
        );
    }

    /**
     * Test that no WEEE data is added when products have no WEEE attributes
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 100, 'weight' => 1],
            'simple1'
        ),
        DataFixture(
            ProductFixture::class,
            ['price' => 150, 'weight' => 1],
            'simple2'
        ),
        DataFixture(
            AttributeFixture::class,
            ['options' => [['label' => 'Option 1'], ['label' => 'Option 2']]],
            'attr'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable-no-weee',
                '_options' => ['$attr$'],
                '_links' => ['$simple1$', '$simple2$']
            ],
            'configurable'
        )
    ]
    public function testNoWeeeDataWhenNoWeeeAttributes(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableProduct = $fixtures->get('configurable');
        $simple1 = $fixtures->get('simple1');
        $simple2 = $fixtures->get('simple2');

        // Enable WEEE
        $this->scopeConfig->setValue(
            'tax/weee/enable',
            1,
            ScopeInterface::SCOPE_STORE
        );

        // Set current product in registry for the block
        $configurableProduct = $this->productRepository->getById($configurableProduct->getId());
        $this->registry->register('product', $configurableProduct);
        $this->registry->register('current_product', $configurableProduct);

        // Get JSON config
        $jsonConfig = $this->configurableBlock->getJsonConfig();
        $config = $this->jsonDecoder->decode($jsonConfig);

        // Assert optionPrices exist
        $this->assertArrayHasKey('optionPrices', $config);
        $this->assertArrayNotHasKey((string)$simple1->getId(), $config['optionPrices']);
        $this->assertArrayNotHasKey((string)$simple2->getId(), $config['optionPrices']);
    }
}
