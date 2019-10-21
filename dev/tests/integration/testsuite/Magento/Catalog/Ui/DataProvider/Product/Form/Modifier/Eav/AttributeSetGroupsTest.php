<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Ui\DataProvider\Mapper\FormElement;
use Magento\Ui\DataProvider\Mapper\MetaProperties;
use PHPUnit\Framework\TestCase;

/**
 * Tests for eav product form modifier for attribute set groups.
 */
class AttributeSetGroupsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LocatorInterface
     */
    private $locatorMock;

    /**
     * @var FormElement
     */
    private $formElement;

    /**
     * @var MetaProperties
     */
    private $metaPropertiesMapper;

    /**
     * @var Eav
     */
    private $productFormModifier;

    /**
     * @var CompositeConfigProcessor
     */
    private $compositeConfigProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $store = $this->objectManager->get(StoreInterface::class);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->locatorMock->expects($this->any())->method('getStore')->willReturn($store);
        $this->formElement = $this->objectManager->create(
            FormElement::class,
            [
                'mappings' => [],
            ]
        );
        $this->metaPropertiesMapper = $this->objectManager->create(
            MetaProperties::class,
            [
                'mappings' => [],
            ]
        );
        $this->compositeConfigProcessor = $this->objectManager->create(
            CompositeConfigProcessor::class,
            [
                'eavWysiwygDataProcessors' => [],
            ]
        );
        $this->productFormModifier = $this->objectManager->create(
            Eav::class,
            [
                'locator' => $this->locatorMock,
                'formElementMapper' => $this->formElement,
                'metaPropertiesMapper' => $this->metaPropertiesMapper,
                'wysiwygConfigProcessor' => $this->compositeConfigProcessor,
            ]
        );
        parent::setUp();
    }

    /**
     * Check that custom group for custom attribute set not added to product form modifier meta data.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_test_attribute_set.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testGroupDoesNotAddToProductFormMeta(): void
    {
        $product = $this->productRepository->get('simple');
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);
        $meta = $this->productFormModifier->modifyMeta([]);
        $this->assertArrayNotHasKey(
            'test-attribute-group-name',
            $meta,
            'Attribute set group without attributes appear on product page in admin panel'
        );
    }
}
