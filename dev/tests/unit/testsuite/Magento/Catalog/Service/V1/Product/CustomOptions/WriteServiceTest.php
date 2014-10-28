<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Service\V1\Product\CustomOptions;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{

    const PRODUCT_SKU = 'simple';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionTypeBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMetadataReaderMock;

    /**
     * @var \Magento\Catalog\Service\V1\Product\CustomOptions\WriteService
     */
    protected $writeService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    protected function setUp()
    {
        $this->optionTypeBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader',
            [],
            [],
            '',
            false
        );

        $this->repositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository',
            [],
            [],
            '',
            false
        );
        $methods = [
            'getOptions',
            'getOptionById',
            'setProductOptions',
            'setHasOptions',
            'save',
            'load',
            'reset',
            'getId',
            '__wakeup',
            'setCanSaveCustomOptions'
        ];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);

        $this->optionConverterMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Converter', [], [], '', false);

        $this->repositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::PRODUCT_SKU)
            ->will($this->returnValue($this->productMock));

        $this->optionBuilderMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\OptionBuilder', [], [], '', false);

        $this->optionMetadataReaderMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ReaderInterface'
        );

        $this->factoryMock = $this->getMock(
            '\Magento\Catalog\Model\Product\OptionFactory', ['create'], [], '', false, false
        );

        $this->optionMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Option',
            ['__sleep', '__wakeup', 'getId', 'getTitle', 'getType', 'delete', 'getIsRequire', 'getSortOrder', 'load'],
            [],
            '',
            false,
            false
        );

        $this->factoryMock->expects($this->any())->method('create')->will($this->returnValue($this->optionMock));

        $this->writeService = new \Magento\Catalog\Service\V1\Product\CustomOptions\WriteService(
            $this->optionBuilderMock,
            $this->optionConverterMock,
            $this->repositoryMock,
            $this->optionMetadataReaderMock,
            $this->factoryMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with optionId = 10
     */
    public function testRemoveFromProductWithoutOptions()
    {
        $this->optionMock->expects($this->once())->method('load')->with(10);
        $this->productMock->expects($this->once())->method('getOptions')->will($this->returnValue(array()));
        $this->productMock->expects($this->never())->method('getOptionById');
        $this->writeService->remove(self::PRODUCT_SKU, 10);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with optionId = 10
     */
    public function testRemoveNotExistingOption()
    {
        $options[1] = [
            Data\Option::OPTION_ID => 10,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
        ];
        $this->productMock->expects($this->once())->method('getOptions')->will($this->returnValue($options));
        $this->optionMock->expects($this->never())->method('delete');
        $this->writeService->remove(self::PRODUCT_SKU, 10);
    }

    public function testSuccessRemove()
    {
        $this->optionMock->expects($this->once())->method('load')->with(10);
        $this->optionMock->expects($this->any())->method('getId')->will($this->returnValue(10));

        $this->productMock
            ->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue([10 => $this->optionMock]));

        $this->optionMock->expects($this->once())->method('delete');
        $this->productMock->expects($this->once())->method('setHasOptions')->with(false);
        $this->productMock->expects($this->once())->method('save');

        $this->assertTrue($this->writeService->remove(self::PRODUCT_SKU, 10));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCanNotRemove()
    {
        $this->optionMock->expects($this->once())->method('load')->with(10);
        $this->optionMock->expects($this->any())->method('getId')->will($this->returnValue(10));

        $this->productMock
            ->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue([10 => $this->optionMock]));

        $this->optionMock->expects($this->once())->method('delete');
        $this->productMock->expects($this->once())->method('setHasOptions')->with(false);
        $this->productMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->writeService->remove(self::PRODUCT_SKU, 10);
    }

    public function testRemoveMetadata()
    {
        $optionId = 231;
        $optionDataMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option',
            ['getValues'],
            [],
            '',
            false
        );

        $optionData = [
            'option_id' => $optionId,
            'values' => [
                ['option_type_id' => 1],
                ['option_type_id' => 2],
            ],
        ];
        $updatedData = $optionData;
        $updatedData['values'][] = ['option_type_id' => 939, 'is_delete' => 1];

        $metaDataMock1 = $this->getMock('\Magento\Catalog\Model\Product\Option\Value', [], [], '', false);
        $metaDataMock2 = $this->getMock('\Magento\Catalog\Model\Product\Option\Value', [], [], '', false);
        $metaDataMock3 = $this->getMock('\Magento\Catalog\Model\Product\Option\Value', [], [], '', false);
        $map1 = [
            ['option_type_id', null, 1],
            ['', null, ['option_type_id' => 1]],
        ];
        $map2 = [
            ['option_type_id', null, 2],
            ['', null, ['option_type_id' => 2]],
        ];
        $map3 = [
            ['option_type_id', null, 939],
            ['', null, ['option_type_id' => 939, 'is_delete' => 1]],
        ];

        $originalValues = [$metaDataMock1, $metaDataMock2, $metaDataMock3];


        $this->optionConverterMock->expects($this->once())
            ->method('convert')
            ->with($optionDataMock)
            ->will($this->returnValue($optionData));
        $this->productMock->expects($this->exactly(2))
            ->method('getOptionById')
            ->will($this->returnValue($optionDataMock));
        $optionDataMock->expects($this->once())->method('getValues')->will($this->returnValue($originalValues));

        // preparation for markValuesForRemoval()
        $metaDataMock1->expects($this->any())->method('getData')->will($this->returnValueMap($map1));
        $metaDataMock2->expects($this->any())->method('getData')->will($this->returnValueMap($map2));
        $metaDataMock3->expects($this->any())->method('getData')->will($this->returnValueMap($map3));
        $metaDataMock3->expects($this->once())->method('setData')->with('is_delete', 1);

        // update()
        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects($this->once())->method('setProductOptions')->with([$updatedData]);
        $this->productMock->expects($this->once())->method('save');

        $this->assertTrue($this->writeService->update(self::PRODUCT_SKU, $optionId, $optionDataMock));
    }

    public function testAdd()
    {
        $optionData = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $convertedOptions =  [
            Data\Option::OPTION_ID => null,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
            'price_type' => 'fixed',
            'sku' => 'sku1',
            'max_characters' => 10
        ];
        $this->optionConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($optionData)
            ->will($this->returnValue($convertedOptions));

        $existingOptions = [1 => null, 2 => null];
        $currentOptions = [1 => null, 2 => null, 10 => $this->optionMock];

        $this->productMock->expects($this->at(2))
            ->method('getOptions')->will($this->returnValue($existingOptions));
        $this->productMock->expects($this->at(7))
            ->method('getOptions')->will($this->returnValue($currentOptions));

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock->expects($this->once())->method('reset');
        $this->productMock->expects($this->once())->method('load')->with(1);

        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects($this->once())->method('setProductOptions')->with([$convertedOptions]);
        $this->productMock->expects($this->once())->method('save');

        $this->optionMock->expects($this->once())->method('getId')->will($this->returnValue(10));
        $this->optionMock->expects($this->once())->method('getTitle')->will($this->returnValue('Some title'));
        $this->optionMock->expects($this->once())->method('getType')->will($this->returnValue('text'));
        $this->optionMock->expects($this->once())->method('getIsRequire')->will($this->returnValue(true));
        $this->optionMock->expects($this->once())->method('getSortOrder')->will($this->returnValue(10));

        $this->optionMetadataReaderMock->expects($this->once())->method('read')->will($this->returnValue('some value'));

        $expectedData = [
            Data\Option::OPTION_ID => 10,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
            'metadata' => 'some value'
        ];

        $this->optionBuilderMock->expects($this->once())
            ->method('populateWithArray')->with($expectedData)->will($this->returnSelf());
        $this->optionBuilderMock->expects($this->once())->method('create')->will($this->returnValue($optionData));

        $this->assertEquals($optionData, $this->writeService->add(self::PRODUCT_SKU, $optionData));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddWithException()
    {
        $optionData = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $convertedOptions =  [
            Data\Option::OPTION_ID => null,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
            'price_type' => 'fixed',
            'sku' => 'sku1',
            'max_characters' => 10
        ];
        $this->optionConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($optionData)
            ->will($this->returnValue($convertedOptions));

        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects($this->once())->method('setProductOptions')->with([$convertedOptions]);
        $this->productMock->expects($this->once())->method('save');

        $existingOptions = [1 => null, 2 => null];
        $currentOptions = [1 => null, 2 => null];

        $this->productMock->expects($this->at(2))
            ->method('getOptions')->will($this->returnValue($existingOptions));
        $this->productMock->expects($this->at(7))
            ->method('getOptions')->will($this->returnValue($currentOptions));

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->productMock->expects($this->once())->method('reset');
        $this->productMock->expects($this->once())->method('load')->with(1);
        $this->writeService->add(self::PRODUCT_SKU, $optionData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddWithExceptionDuringSave()
    {
        $optionData = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $convertedOptions =  [
            Data\Option::OPTION_ID => 10,
            Data\Option::TITLE => 'Some title',
            Data\Option::TYPE => 'text',
            Data\Option::IS_REQUIRE => true,
            Data\Option::SORT_ORDER => 10,
            'price_type' => 'fixed',
            'sku' => 'sku1',
            'max_characters' => 10
        ];
        $this->optionConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($optionData)
            ->will($this->returnValue($convertedOptions));

        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects($this->once())->method('setProductOptions')->with([$convertedOptions]);
        $this->productMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->writeService->add(self::PRODUCT_SKU, $optionData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testAddWithOptionId()
    {
        $optionData = $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $optionData->expects($this->once())->method('getOptionId')->will($this->returnValue(10));
        $this->optionConverterMock
            ->expects($this->never())
            ->method('convert');
        $this->writeService->add(self::PRODUCT_SKU, $optionData);
    }
}
