<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate as LayoutUpdateAttribute;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav as EavModifier;

/**
 * Test the modifier.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LayoutUpdateTest extends TestCase
{
    /**
     * @var LayoutUpdate
     */
    private $modifier;

    /**
     * @var ProductRepositoryInterface
     */
    private $repo;

    /**
     * @var MockObject
     */
    private $locator;

    /**
     * @var EavModifier
     */
    private $eavModifier;

    /**
     * @var ProductLayoutUpdateManager
     */
    private $fakeFiles;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                \Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager::class =>
                    \Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager::class
            ]
        ]);
        $this->locator = $this->getMockForAbstractClass(LocatorInterface::class);
        $store = Bootstrap::getObjectManager()->create(StoreInterface::class);
        $this->locator->method('getStore')->willReturn($store);
        $this->modifier = Bootstrap::getObjectManager()->create(LayoutUpdate::class, ['locator' => $this->locator]);
        $this->repo = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->eavModifier = Bootstrap::getObjectManager()->create(
            EavModifier::class,
            [
                'locator' => $this->locator,
                'formElementMapper' => Bootstrap::getObjectManager()->create(
                    \Magento\Ui\DataProvider\Mapper\FormElement::class,
                    [
                        'mappings' => [
                            "text" => "input",
                            "hidden" => "input",
                            "boolean" => "checkbox",
                            "media_image" => "image",
                            "price" => "input",
                            "weight" => "input",
                            "gallery" => "image"
                        ]
                    ]
                )
            ]
        );
        $this->fakeFiles = Bootstrap::getObjectManager()->get(ProductLayoutUpdateManager::class);
    }

    /**
     * Test that data is being modified accordingly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     * @throws \Throwable
     */
    public function testModifyData(): void
    {
        $product = $this->repo->get('simple');
        $this->locator->method('getProduct')->willReturn($product);
        $product->setCustomAttribute('custom_layout_update', 'something');

        $data = $this->modifier->modifyData([$product->getId() => ['product' => []]]);
        $this->assertEquals(
            LayoutUpdateAttribute::VALUE_USE_UPDATE_XML,
            $data[$product->getId()]['product']['custom_layout_update_file']
        );
    }

    /**
     * Extract options meta.
     *
     * @param array $meta
     * @return array
     */
    private function extractCustomLayoutOptions(array $meta): array
    {
        $this->assertArrayHasKey('design', $meta);
        $this->assertArrayHasKey('children', $meta['design']);
        $this->assertArrayHasKey('container_custom_layout_update_file', $meta['design']['children']);
        $this->assertArrayHasKey('children', $meta['design']['children']['container_custom_layout_update_file']);
        $this->assertArrayHasKey(
            'custom_layout_update_file',
            $meta['design']['children']['container_custom_layout_update_file']['children']
        );
        $fieldMeta = $meta['design']['children']['container_custom_layout_update_file']['children'];
        $fieldMeta = $fieldMeta['custom_layout_update_file'];
        $this->assertArrayHasKey('arguments', $fieldMeta);
        $this->assertArrayHasKey('data', $fieldMeta['arguments']);
        $this->assertArrayHasKey('config', $fieldMeta['arguments']['data']);
        $this->assertArrayHasKey('options', $fieldMeta['arguments']['data']['config']);

        return $fieldMeta['arguments']['data']['config']['options'];
    }

    /**
     * Check that entity specific options are returned.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testEntitySpecificData(): void
    {
        //Testing a category without layout xml
        $product = $this->repo->get('simple');
        $this->locator->method('getProduct')->willReturn($product);
        $this->fakeFiles->setFakeFiles((int)$product->getId(), ['testOne', 'test_two']);

        $meta = $this->eavModifier->modifyMeta([]);
        $list = $this->extractCustomLayoutOptions($meta);
        $expectedList = [
            [
                'label' => 'No update',
                'value' => \Magento\Catalog\Model\Attribute\Backend\AbstractLayoutUpdate::VALUE_NO_UPDATE,
                '__disableTmpl' => true
            ],
            ['label' => 'testOne', 'value' => 'testOne', '__disableTmpl' => true],
            ['label' => 'test_two', 'value' => 'test_two', '__disableTmpl' => true]
        ];
        sort($expectedList);
        sort($list);
        $this->assertEquals($expectedList, $list);

        //Product with old layout xml
        $product->setCustomAttribute('custom_layout_update', 'test');
        $this->fakeFiles->setFakeFiles((int)$product->getId(), ['test3']);

        $meta = $this->eavModifier->modifyMeta([]);
        $list = $this->extractCustomLayoutOptions($meta);
        $expectedList = [
            [
                'label' => 'No update',
                'value' => \Magento\Catalog\Model\Attribute\Backend\AbstractLayoutUpdate::VALUE_NO_UPDATE,
                '__disableTmpl' => true
            ],
            [
                'label' => 'Use existing',
                'value' => LayoutUpdateAttribute::VALUE_USE_UPDATE_XML,
                '__disableTmpl' => true
            ],
            ['label' => 'test3', 'value' => 'test3', '__disableTmpl' => true],
        ];
        sort($expectedList);
        sort($list);
        $this->assertEquals($expectedList, $list);
    }
}
