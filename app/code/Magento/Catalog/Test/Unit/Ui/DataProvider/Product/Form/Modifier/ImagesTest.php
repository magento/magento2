<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;

/**
 * @method \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images getModel
 */
class ImagesTest extends AbstractModifierTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Images::class, [
            'locator' => $this->locatorMock,
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
