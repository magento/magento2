<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Eav\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests that configurable attribute options are resolved for the values used by the product only.
 *
 * @see AttributeOptionProvider
 */
class AttributeOptionProviderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeOptionProvider
     */
    private $model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(AttributeOptionProvider::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->eavConfig = $this->objectManager->get(Config::class);
    }

    /**
     * Only the options assigned to the child products are returned, with their labels resolved.
     */
    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(
            AttributeFixture::class,
            ['options' => ['option_1', 'option_2', 'option_3', 'option_4']],
            'attr'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['sku' => 'configurable_with_unused_options', '_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'conf'
        ),
    ]
    public function testGetAttributeOptionsReturnsUsedOptionsOnly(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $attributeCode = $fixtures->get('attr')->getAttributeCode();
        $configurable = $this->productRepository->get('configurable_with_unused_options');
        $superAttribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        // the default source model applies to every int/select product attribute
        $this->assertNotEmpty(
            $superAttribute->getSourceModel(),
            'The attribute is expected to resolve a source model, otherwise the tested branch is skipped.'
        );

        $options = $this->model->getAttributeOptions($superAttribute, (int) $configurable->getId());

        $this->assertCount(2, $options, 'Only the options used by the child products are expected.');

        $expectedLabels = [];
        foreach (['option_1', 'option_2'] as $label) {
            $expectedLabels[(string) $fixtures->get('attr')->getData($label)] = $label;
        }

        foreach ($options as $option) {
            $valueIndex = (string) $option['value_index'];
            $this->assertArrayHasKey($valueIndex, $expectedLabels, 'An unused option was returned.');
            $this->assertSame($expectedLabels[$valueIndex], $option['option_title']);
            $this->assertSame($expectedLabels[$valueIndex], $option['default_title']);
        }
    }
}
