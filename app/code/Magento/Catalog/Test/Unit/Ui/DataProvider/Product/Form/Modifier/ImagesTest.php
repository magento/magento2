<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product\Gallery\DefaultValueProcessor;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;

/**
 * @method \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images getModel
 */
class ImagesTest extends AbstractModifierTestCase
{
    /**
     * @var DefaultValueProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $defaultValueProcessorMock;

    /**
     * @var ScopeOverriddenValue|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeOverriddenValueMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->defaultValueProcessorMock = $this->createMock(DefaultValueProcessor::class);
        $this->scopeOverriddenValueMock = $this->createMock(ScopeOverriddenValue::class);
        
        // Mock defaultValueProcessor to return the input data unchanged
        $this->defaultValueProcessorMock->method('process')
            ->willReturnArgument(1);
        
        // Mock scopeOverriddenValue to return false (value not overridden)
        $this->scopeOverriddenValueMock->method('containsValue')
            ->willReturn(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Images::class, [
            'locator' => $this->locatorMock,
            'defaultValueProcessor' => $this->defaultValueProcessorMock,
            'scopeOverriddenValue' => $this->scopeOverriddenValueMock,
        ]);
    }

    public function testModifyData()
    {
        $this->productMock->setId(2051);
        $actualResult = $this->getModel()->modifyData($this->getSampleData());
        $this->assertSame("", $actualResult[2051]['product']['media_gallery']['images'][0]['label']);
    }

    public function testModifyMeta()
    {
        $meta = [
            Images::CODE_IMAGE_MANAGEMENT_GROUP => [
                'children' => [],
                'label' => __('Images'),
                'sortOrder' => '20',
                'componentType' => 'fieldset'
            ]
        ];

        $this->assertSame([], $this->getModel()->modifyMeta($meta));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSampleData()
    {
        return [
            2051 => [
                'product' => [
                    'media_gallery' => [
                        'images' => [
                            [
                                'label' => null
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
