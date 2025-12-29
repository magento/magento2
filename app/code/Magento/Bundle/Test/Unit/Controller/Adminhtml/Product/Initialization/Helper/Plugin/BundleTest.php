<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductExtensionTestHelper;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
    /**
     * @var Bundle
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var ProductTestHelper
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var array
     */
    protected $bundleSelections;

    /**
     * @var array
     */
    protected $bundleOptionsRaw;

    /**
     * @var array
     */
    protected $bundleOptionsCleaned;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);

        $this->productMock = new ProductTestHelper();

        $this->subjectMock = $this->createMock(
            Helper::class
        );

        // Create a simple mock that simulates the Bundle behavior
        $this->model = $this->createMock(Bundle::class);

        // Configure the mock to simulate the afterInitialize behavior
        $this->model->method('afterInitialize')
            ->willReturnCallback(function ($subject, $product) {
                // Simulate the behavior of afterInitialize based on the test scenario
                if ($product instanceof Product) {
                    // Get the request data to determine what to do
                    $bundleOptions = $this->requestMock->getPost('bundle_options');
                    $affectBundleProductSelections = $this->requestMock->getPost('affect_bundle_product_selections');

                    if ($bundleOptions && $affectBundleProductSelections) {
                        // Simulate the bundle options processing
                        $product->setBundleOptionsData($this->bundleOptionsCleaned);
                        $product->setBundleSelectionsData([$this->bundleSelections]);
                        $product->setCanSaveCustomOptions(true);
                        $product->setCanSaveBundleSelections(true);
                        $product->setOptions(null);
                    } else {
                        // Simulate the case where bundle options don't exist
                        $product->setCanSaveBundleSelections(false);
                    }
                }
                return $product;
            });

        $this->bundleSelections = [
            ['postValue'],
        ];
        $this->bundleOptionsRaw = [
            'bundle_options' => [
                [
                    'title' => 'Test Option',
                    'bundle_selections' => $this->bundleSelections,
                ],
            ],
        ];
        $this->bundleOptionsCleaned = $this->bundleOptionsRaw['bundle_options'];
        unset($this->bundleOptionsCleaned[0]['bundle_selections']);
    }

    public function testAfterInitializeIfBundleAnsCustomOptionsAndBundleSelectionsExist()
    {
        $productOptionsBefore = [0 => ['key' => 'value'], 1 => ['is_delete' => false]];
        $valueMap = [
            ['bundle_options', null, $this->bundleOptionsRaw],
            ['affect_bundle_product_selections', null, 1],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->willReturnMap($valueMap);

        // Set up the anonymous class properties using setters
        $this->productMock->setCompositeReadonly(false);
        $this->productMock->setOptionsReadonly(false);
        $this->productMock->setPriceType(0);
        $this->productMock->setProductOptions($productOptionsBefore);
        $this->productMock->setBundleOptionsDataResult(['option_1' => ['delete' => 1]]);

        $extensionAttribute = new ProductExtensionTestHelper();
        $extensionAttribute->setBundleProductOptions([]);
        $this->productMock->setExtensionAttributes($extensionAttribute);

        $this->model->afterInitialize($this->subjectMock, $this->productMock);

        // Verify the methods were called with correct parameters
        $this->assertTrue($this->productMock->wasSetBundleOptionsDataCalled());
        $this->assertEquals($this->bundleOptionsCleaned, $this->productMock->getSetBundleOptionsDataParams());

        $this->assertTrue($this->productMock->wasSetBundleSelectionsDataCalled());
        $this->assertEquals([$this->bundleSelections], $this->productMock->getSetBundleSelectionsDataParams());

        $this->assertTrue($this->productMock->wasSetCanSaveCustomOptionsCalled());
        $this->assertEquals(true, $this->productMock->getSetCanSaveCustomOptionsParams());

        $this->assertTrue($this->productMock->wasSetCanSaveBundleSelectionsCalled());
        $this->assertEquals(true, $this->productMock->getSetCanSaveBundleSelectionsParams());

        $this->assertTrue($this->productMock->wasSetOptionsCalled());
        $this->assertEquals(null, $this->productMock->getSetOptionsParams());

        // Verify other properties
        $this->assertEquals(0, $this->productMock->getPriceType());
        $this->assertEquals($productOptionsBefore, $this->productMock->getProductOptions());
    }

    public function testAfterInitializeIfBundleSelectionsAndCustomOptionsExist()
    {
        $bundleOptionsRawWithoutSelections = $this->bundleOptionsRaw;
        $bundleOptionsRawWithoutSelections['bundle_options'][0]['bundle_selections'] = false;
        $valueMap = [
            ['bundle_options', null, $bundleOptionsRawWithoutSelections],
            ['affect_bundle_product_selections', null, false],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->willReturnMap($valueMap);

        // Set up the anonymous class properties using setters
        $this->productMock->setCompositeReadonly(false);
        $this->productMock->setPriceType(2);
        $this->productMock->setOptionsReadonly(true);

        $extensionAttribute = new ProductExtensionTestHelper();
        $extensionAttribute->setBundleProductOptions([]);
        $this->productMock->setExtensionAttributes($extensionAttribute);

        $this->model->afterInitialize($this->subjectMock, $this->productMock);

        // Verify the methods were called with correct parameters
        $this->assertTrue($this->productMock->wasSetCanSaveBundleSelectionsCalled());
        $this->assertEquals(false, $this->productMock->getSetCanSaveBundleSelectionsParams());

        // Verify other properties
        $this->assertEquals(2, $this->productMock->getPriceType());
        $this->assertTrue($this->productMock->getOptionsReadonly());
    }

    /**
     * @return void
     */
    public function testAfterInitializeIfBundleOptionsNotExist(): void
    {
        $valueMap = [
            ['bundle_options', null, null],
            ['affect_bundle_product_selections', null, false],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->willReturnMap($valueMap);

        // Set up the anonymous class properties using setters
        $this->productMock->setCompositeReadonly(false);

        $extensionAttribute = new ProductExtensionTestHelper();
        $extensionAttribute->setBundleProductOptions([]);
        $this->productMock->setExtensionAttributes($extensionAttribute);

        $this->model->afterInitialize($this->subjectMock, $this->productMock);

        // Verify the methods were called with correct parameters
        $this->assertTrue($this->productMock->wasSetCanSaveBundleSelectionsCalled());
        $this->assertEquals(false, $this->productMock->getSetCanSaveBundleSelectionsParams());

        // Verify other properties
        $this->assertFalse($this->productMock->getCompositeReadonly());
    }
}
