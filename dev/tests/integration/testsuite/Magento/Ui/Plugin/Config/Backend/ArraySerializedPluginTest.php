<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Plugin\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Integration test for ArraySerializedPlugin
 */
class ArraySerializedPluginTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test that plugin converts string keys to numeric keys. Works only on design config edit page
     */
    #[AppArea('adminhtml')]
    #[DataProvider('serializeDataProvider')]
    public function testPluginConvertsStringKeysOnDesignConfigEditPage($stringKeyedArray, $resultArray)
    {
        // Set up the request to simulate design config edit page
        $this->setPageContext();

        // Create ArraySerialized backend model
        $backendModel = $this->objectManager->create(ArraySerialized::class);
        $backendModel->setValue($stringKeyedArray);

        // Trigger the afterAfterLoad method (this is where the plugin should run)
        $backendModel->afterLoad();

        // Get the processed value
        $processedValue = $backendModel->getValue();

        // Assert that string keys were converted to numeric keys
        $this->assertEquals($resultArray, $processedValue);
    }

    /**
     * Test that plugin doesn't convert data when not on design config edit page
     */
    #[
        AppArea('adminhtml')
    ]
    public function testPluginDoesNotConvertOnOtherPages()
    {
        // Create test data with string keys
        $stringKeyedArray = [
            'row1' => ['field1' => 'value1'],
            'row2' => ['field2' => 'value2'],
            'row3' => ['field3' => 'value3']
        ];

        // Create ArraySerialized backend model
        $backendModel = $this->objectManager->create(ArraySerialized::class);
        $backendModel->setValue($stringKeyedArray);

        // Trigger the afterAfterLoad method
        $backendModel->afterLoad();

        // Get the processed value
        $processedValue = $backendModel->getValue();

        // Assert that string keys are preserved (no conversion should happen)
        $this->assertIsArray($processedValue);
        $this->assertEquals($stringKeyedArray, $processedValue);
    }

    /**
     * Test that plugin doesn't convert data in frontend area
     */
    #[
        AppArea('frontend')
    ]
    public function testPluginDoesNotConvertInFrontendArea()
    {
        // Set up the request to simulate design config edit page
        $this->setPageContext();

        // Create test data with string keys
        $stringKeyedArray = [
            'row1' => ['field1' => 'value1'],
            'row2' => ['field2' => 'value2'],
            'row3' => ['field3' => 'value3']
        ];

        // Create ArraySerialized backend model
        $backendModel = $this->objectManager->create(ArraySerialized::class);
        $backendModel->setValue($stringKeyedArray);

        // Trigger the afterAfterLoad method
        $backendModel->afterLoad();

        // Get the processed value
        $processedValue = $backendModel->getValue();

        // Assert that string keys are preserved (no conversion should happen in frontend)
        $this->assertIsArray($processedValue);
        $this->assertEquals($stringKeyedArray, $processedValue);
    }

    /**
     * Data provider for ArraySerializedPlugin. Input array and resultant array.
     */
    public static function serializeDataProvider(): array
    {
        return [
            [ //Test that plugin converts string keys to numeric keys.
                [
                    'row1' => ['field1' => 'value1'],
                    'row2' => ['field2' => 'value2'],
                    'row3' => ['field3' => 'value3']
                ],
                [
                    ['field1' => 'value1'],
                    ['field2' => 'value2'],
                    ['field3' => 'value3']
                ]
            ],
            [ //Test that plugin doesn't convert already numeric arrays.
                [
                    ['field1' => 'value1'],
                    ['field2' => 'value2'],
                    ['field3' => 'value3']
                ],
                [
                    ['field1' => 'value1'],
                    ['field2' => 'value2'],
                    ['field3' => 'value3']
                ]
            ],
            [ // Test that plugin handles empty arrays correctly
                [],
                []
            ],
            [ // Test that plugin handles non-array values correctly
                'test_string',
                false
            ]
        ];
    }

    /**
     * Set up the request to simulate design config edit page
     *
     * @return void
     */
    private function setPageContext()
    {
        $this->getRequest()->setModuleName('theme');
        $this->getRequest()->setControllerName('design_config');
        $this->getRequest()->setActionName('edit');
    }
}
