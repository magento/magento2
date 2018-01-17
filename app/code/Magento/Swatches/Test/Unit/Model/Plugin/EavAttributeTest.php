<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Swatches\Model\Plugin\EavAttribute;
use Magento\Swatches\Model\Swatch;

class EavAttributeTest extends \PHPUnit\Framework\TestCase
{
    const ATTRIBUTE_ID = 123;
    const OPTION_ID = 'option 12';
    const STORE_ID = 'option 89';
    const ATTRIBUTE_DEFAULT_VALUE = 1;
    const ATTRIBUTE_OPTION_VALUE = 2;
    const ATTRIBUTE_SWATCH_VALUE = 3;

    /** @var EavAttribute */
    private $eavAttribute;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    /** @var \Magento\Swatches\Model\SwatchFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchFactory;

    /** @var \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $collectionFactory;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchHelper;

    /** @var \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource|\PHPUnit_Framework_MockObject_MockObject */
    private $abstractSource;

    /** @var \Magento\Swatches\Model\Swatch|\PHPUnit_Framework_MockObject_MockObject */
    private $swatch;

    /** @var \Magento\Swatches\Model\ResourceModel\Swatch|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection|\PHPUnit_Framework_MockObject_MockObject */
    private $collection;

    /** @var array */
    private $optionIds = [];

    /** @var array */
    private $allOptions = [];

    /** @var array */
    private $dependencyArray = [];

    protected function setUp()
    {
        $this->attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->swatchFactory = $this->createPartialMock(\Magento\Swatches\Model\SwatchFactory::class, ['create']);
        $this->swatchHelper = $this->createMock(\Magento\Swatches\Helper\Data::class);
        $this->swatch = $this->createMock(\Magento\Swatches\Model\Swatch::class);
        $this->resource = $this->createMock(\Magento\Swatches\Model\ResourceModel\Swatch::class);
        $this->collection =
            $this->createMock(\Magento\Swatches\Model\ResourceModel\Swatch\Collection::class);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory::class,
            ['create']
        );
        $this->abstractSource = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class);

        $serializer = $this->createPartialMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            ['serialize', 'unserialize']
        );

        $serializer->expects($this->any())
            ->method('serialize')->willReturnCallback(function ($parameter) {
                return json_encode($parameter);
            });

        $serializer->expects($this->any())
            ->method('unserialize')->willReturnCallback(function ($parameter) {
                return json_decode($parameter, true);
            });

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eavAttribute = $objectManager->getObject(
            \Magento\Swatches\Model\Plugin\EavAttribute::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'swatchFactory' => $this->swatchFactory,
                'swatchHelper' => $this->swatchHelper,
                'serializer' => $serializer,
            ]
        );

        $this->optionIds = [
            'value' => ['option 89' => 'test 1', 'option 114' => 'test 2', 'option 170' => 'test 3'],
            'delete' => ['option 89' => 0, 'option 114' => 1, 'option 170' => 0],
        ];
        $this->allOptions = [null, ['value' => 'option 12'], ['value' => 'option 154']];
        $this->dependencyArray = ['option 89', 'option 170'];
    }

    public function testBeforeSaveVisualSwatch()
    {
        $option = [
            'value' => [
                0 => 'option value',
            ]
        ];
        $this->attribute->expects($this->exactly(6))->method('getData')->withConsecutive(
            ['defaultvisual'],
            ['optionvisual'],
            ['swatchvisual'],
            ['optionvisual'],
            ['option/delete/0']
        )->will($this->onConsecutiveCalls(
            self::ATTRIBUTE_DEFAULT_VALUE,
            self::ATTRIBUTE_OPTION_VALUE,
            self::ATTRIBUTE_SWATCH_VALUE,
            $option,
            false
        ));

        $this->attribute->expects($this->exactly(3))->method('setData')
            ->withConsecutive(
                ['option', self::ATTRIBUTE_OPTION_VALUE],
                ['default', self::ATTRIBUTE_DEFAULT_VALUE],
                ['swatch', self::ATTRIBUTE_SWATCH_VALUE]
            );

        $this->swatchHelper->expects($this->once())->method('assembleAdditionalDataEavAttribute')
            ->with($this->attribute);
        $this->swatchHelper->expects($this->atLeastOnce())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->never())->method('isTextSwatch');

        $this->eavAttribute->beforeBeforeSave($this->attribute);
    }

    public function testBeforeSaveTextSwatch()
    {
        $option = [
            'value' => [
                0 => 'option value',
            ]
        ];
        $this->attribute->expects($this->exactly(6))->method('getData')->withConsecutive(
            ['defaulttext'],
            ['optiontext'],
            ['swatchtext'],
            ['optiontext'],
            ['option/delete/0']
        )->will(
            $this->onConsecutiveCalls(
                self::ATTRIBUTE_DEFAULT_VALUE,
                self::ATTRIBUTE_OPTION_VALUE,
                self::ATTRIBUTE_SWATCH_VALUE,
                $option,
                false
            )
        );

        $this->attribute->expects($this->exactly(3))->method('setData')
            ->withConsecutive(
                ['option', self::ATTRIBUTE_OPTION_VALUE],
                ['default', self::ATTRIBUTE_DEFAULT_VALUE],
                ['swatch', self::ATTRIBUTE_SWATCH_VALUE]
            );

        $this->swatchHelper->expects($this->once())->method('assembleAdditionalDataEavAttribute')
            ->with($this->attribute);
        $this->swatchHelper->expects($this->atLeastOnce())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->atLeastOnce())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);

        $this->eavAttribute->beforeBeforeSave($this->attribute);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Admin is a required field in each row
     */
    public function testBeforeSaveWithFailedValidation()
    {
        $optionText = [
            'value' => [
                0 => '',
            ]
        ];
        $this->swatchHelper->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);

        $this->swatchHelper->expects($this->atLeastOnce())
            ->method('isVisualSwatch')
            ->willReturn(true);
        $this->attribute->expects($this->exactly(5))
            ->method('getData')
            ->withConsecutive(
                ['defaultvisual'],
                ['optionvisual'],
                ['swatchvisual'],
                ['optionvisual'],
                ['option/delete/0']
            )
            ->will(
                $this->onConsecutiveCalls(
                    self::ATTRIBUTE_DEFAULT_VALUE,
                    self::ATTRIBUTE_OPTION_VALUE,
                    self::ATTRIBUTE_SWATCH_VALUE,
                    $optionText,
                    false
                )
            );

        $this->eavAttribute->beforeBeforeSave($this->attribute);
    }

    /**
     * @covers \Magento\Swatches\Model\Plugin\EavAttribute::beforeBeforeSave()
     */
    public function testBeforeSaveWithDeletedOption()
    {
        $optionText = [
            'value' => [
                0 => '',
            ]
        ];

        $this->swatchHelper->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);

        $this->swatchHelper->expects($this->atLeastOnce())
            ->method('isVisualSwatch')
            ->willReturn(true);
        $this->attribute->expects($this->exactly(6))
            ->method('getData')
            ->withConsecutive(
                ['defaultvisual'],
                ['optionvisual'],
                ['swatchvisual'],
                ['optionvisual'],
                ['option/delete/0'],
                ['swatch_input_type']
            )
            ->will(
                $this->onConsecutiveCalls(
                    self::ATTRIBUTE_DEFAULT_VALUE,
                    self::ATTRIBUTE_OPTION_VALUE,
                    self::ATTRIBUTE_SWATCH_VALUE,
                    $optionText,
                    true,
                    false
                )
            );
        $this->eavAttribute->beforeBeforeSave($this->attribute);
    }

    public function testBeforeSaveNotSwatch()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        $shortAdditionalData = [
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        $this->attribute->expects($this->exactly(2))->method('getData')->withConsecutive(
            [Swatch::SWATCH_INPUT_TYPE_KEY],
            ['additional_data']
        )->willReturnOnConsecutiveCalls(
            Swatch::SWATCH_INPUT_TYPE_DROPDOWN,
            json_encode($additionalData)
        );

        $this->attribute
            ->expects($this->once())
            ->method('setData')
            ->with('additional_data', json_encode($shortAdditionalData))
            ->will($this->returnSelf());

        $this->swatchHelper->expects($this->never())->method('assembleAdditionalDataEavAttribute');
        $this->swatchHelper->expects($this->never())->method('isVisualSwatch');
        $this->swatchHelper->expects($this->never())->method('isTextSwatch');

        $this->swatchHelper->expects($this->once())->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(false);

        $this->eavAttribute->beforeBeforeSave($this->attribute);
    }

    public function visualSwatchProvider()
    {
        return [
            [Swatch::SWATCH_TYPE_EMPTY, null],
            [Swatch::SWATCH_TYPE_VISUAL_COLOR, '#hex'],
            [Swatch::SWATCH_TYPE_VISUAL_IMAGE, '/path'],
        ];
    }

    /**
     * @dataProvider visualSwatchProvider
     *
     * @param $swatchType
     * @param $swatchValue
     */
    public function testAfterAfterSaveVisualSwatch($swatchType, $swatchValue)
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);
        $this->resource->expects($this->once())->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_ID);

        $this->swatch->expects($this->once())->method('getResource')
            ->willReturn($this->resource);
        $this->swatch->expects($this->once())->method('getId')
            ->willReturn(EavAttribute::DEFAULT_STORE_ID);
        $this->swatch->expects($this->once())->method('save');
        $this->swatch->expects($this->exactly(4))->method('setData')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', EavAttribute::DEFAULT_STORE_ID],
                ['type', $swatchType],
                ['value', $swatchValue]
            );

        $this->collection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', EavAttribute::DEFAULT_STORE_ID]
            )->willReturnSelf();

        $this->collection->expects($this->once())->method('getFirstItem')
            ->willReturn($this->swatch);
        $this->collectionFactory->expects($this->once())->method('create')
            ->willReturn($this->collection);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn($this->dependencyArray[0]);
        $this->attribute->expects($this->at(3))->method('getId')
            ->willReturn(self::ATTRIBUTE_ID);
        $this->attribute->expects($this->at(4))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => $swatchValue]);
        $this->attribute->expects($this->at(5))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(false);

        $this->swatchFactory->expects($this->exactly(1))->method('create')
            ->willReturn($this->swatch);
        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->never())->method('isTextSwatch');

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testDefaultTextualSwatchAfterSave()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);

        $this->swatch->expects($this->any())->method('getId')
            ->willReturn(EavAttribute::DEFAULT_STORE_ID);
        $this->swatch->expects($this->any())->method('save');
        $this->swatch->expects($this->any())->method('isDeleted')
            ->with(false);

        $this->collection->expects($this->any())->method('addFieldToFilter')
            ->willReturnSelf();
        $this->collection->expects($this->any())->method('getFirstItem')
            ->willReturn($this->swatch);
        $this->collectionFactory->expects($this->any())->method('create')
            ->willReturn($this->collection);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn(null);

        $this->attribute->expects($this->at(3))->method('getData')
            ->with('swatch/value')
            ->willReturn(
                [
                    self::STORE_ID => [
                        1 => "test",
                        2 => false,
                        3 => null,
                        4 => "",
                    ]
                ]
            );

        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->once())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);

        $this->swatch->expects($this->any())->method('setData')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', 1],
                ['type', Swatch::SWATCH_TYPE_TEXTUAL],
                ['value', "test"]
            );

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testAfterAfterSaveTextualSwatch()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);
        $this->resource->expects($this->once())->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_ID);

        $this->swatch->expects($this->once())->method('getResource')
            ->willReturn($this->resource);
        $this->swatch->expects($this->once())->method('getId')
            ->willReturn(EavAttribute::DEFAULT_STORE_ID);
        $this->swatch->expects($this->once())->method('save');
        $this->swatch->expects($this->once())->method('isDeleted')
            ->with(false);
        $this->swatch->expects($this->exactly(4))->method('setData')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', self::OPTION_ID],
                ['type', Swatch::SWATCH_TYPE_TEXTUAL],
                ['value', null]
            );

        $this->collection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', self::OPTION_ID]
            )->willReturnSelf();
        $this->collection->expects($this->once())->method('getFirstItem')
            ->willReturn($this->swatch);
        $this->collectionFactory->expects($this->once())->method('create')
            ->willReturn($this->collection);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn($this->dependencyArray[0]);
        $this->attribute->expects($this->at(3))->method('getId')
            ->willReturn(self::ATTRIBUTE_ID);
        $this->attribute->expects($this->at(4))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => [self::OPTION_ID => null]]);
        $this->attribute->expects($this->at(5))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(false);

        $this->swatchFactory->expects($this->exactly(1))->method('create')
            ->willReturn($this->swatch);
        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->once())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testAfterAfterSaveVisualSwatchIsDelete()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);
        $this->resource->expects($this->once())->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_ID);

        $this->swatch->expects($this->once())->method('getResource')
            ->willReturn($this->resource);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn($this->dependencyArray[0]);
        $this->attribute->expects($this->at(3))->method('getId')
            ->willReturn(self::ATTRIBUTE_ID);
        $this->attribute->expects($this->at(4))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => null]);
        $this->attribute->expects($this->at(5))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(true);

        $this->swatchFactory->expects($this->once())->method('create')
            ->willReturn($this->swatch);
        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->never())->method('isTextSwatch');

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testAfterAfterSaveTextualSwatchIsDelete()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);
        $this->resource->expects($this->once())->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_ID);

        $this->swatch->expects($this->once())->method('getResource')
            ->willReturn($this->resource);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn($this->dependencyArray[0]);
        $this->attribute->expects($this->at(3))->method('getId')
            ->willReturn(self::ATTRIBUTE_ID);
        $this->attribute->expects($this->at(4))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => [self::OPTION_ID => null]]);
        $this->attribute->expects($this->at(5))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(true);

        $this->swatchFactory->expects($this->once())->method('create')
            ->willReturn($this->swatch);
        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->once())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testAfterAfterSaveIsSwatchExists()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);
        $this->resource->expects($this->once())->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_ID);

        $this->swatch->expects($this->once())->method('getResource')
            ->willReturn($this->resource);
        $this->swatch->expects($this->once())->method('getId')
            ->willReturn(1);
        $this->swatch->expects($this->once())->method('save');
        $this->swatch->expects($this->once())->method('isDeleted')
            ->with(false);
        $this->swatch->expects($this->exactly(2))->method('setData')
            ->withConsecutive(
                ['type', Swatch::SWATCH_TYPE_TEXTUAL],
                ['value', null]
            );

        $this->collection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', self::OPTION_ID]
            )->willReturnSelf();
        $this->collection->expects($this->once())->method('getFirstItem')
            ->willReturn($this->swatch);
        $this->collectionFactory->expects($this->once())->method('create')
            ->willReturn($this->collection);

        $this->attribute->expects($this->at(0))->method('getData')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('default/0')
            ->willReturn($this->dependencyArray[0]);
        $this->attribute->expects($this->at(3))->method('getId')
            ->willReturn(self::ATTRIBUTE_ID);
        $this->attribute->expects($this->at(4))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => [self::OPTION_ID => null]]);
        $this->attribute->expects($this->at(5))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(false);

        $this->swatchFactory->expects($this->exactly(1))->method('create')
            ->willReturn($this->swatch);
        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->willReturn(true);
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->once())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);

        $this->eavAttribute->afterAfterSave($this->attribute);
    }

    public function testAfterAfterSaveNotSwatchAttribute()
    {
        $this->abstractSource->expects($this->once())->method('getAllOptions')
            ->willReturn($this->allOptions);

        $this->swatch->expects($this->once())->method('getId')
            ->willReturn(1);
        $this->swatch->expects($this->once())->method('save');
        $this->swatch->expects($this->once())->method('isDeleted')
            ->with(false);
        $this->swatch->expects($this->exactly(2))->method('setData')
            ->withConsecutive(
                ['type', Swatch::SWATCH_TYPE_TEXTUAL],
                ['value', null]
            );

        $this->collection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_ID],
                ['store_id', self::OPTION_ID]
            )->willReturnSelf();
        $this->collection->expects($this->once())->method('getFirstItem')
            ->willReturn($this->swatch);
        $this->collectionFactory->expects($this->once())->method('create')
            ->willReturn($this->collection);

        $this->attribute->expects($this->at(0))->method('getData')
            ->with('option')
            ->willReturn($this->optionIds);
        $this->attribute->expects($this->at(1))->method('getSource')
            ->willReturn($this->abstractSource);
        $this->attribute->expects($this->at(2))->method('getData')
            ->with('swatch/value')
            ->willReturn([self::STORE_ID => [self::OPTION_ID => null]]);
        $this->attribute->expects($this->at(3))->method('getData')
            ->with('option/delete/' . self::OPTION_ID)
            ->willReturn(false);

        $this->swatchHelper->expects($this->exactly(2))->method('isSwatchAttribute')
            ->with($this->attribute)
            ->will($this->onConsecutiveCalls(true, false));
        $this->swatchHelper->expects($this->once())->method('isVisualSwatch')
            ->with($this->attribute)
            ->willReturn(false);
        $this->swatchHelper->expects($this->once())->method('isTextSwatch')
            ->with($this->attribute)
            ->willReturn(true);

        $this->eavAttribute->afterAfterSave($this->attribute);
    }
}
