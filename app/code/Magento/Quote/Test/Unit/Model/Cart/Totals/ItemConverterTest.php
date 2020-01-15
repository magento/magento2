<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Cart\Totals;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemConverterTest extends TestCase
{
    /**
     * @var ItemConverter
     */
    private $model;

    /**
     * @var ConfigurationPool|MockObject
     */
    private $configurationPoolMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var TotalsItemInterfaceFactory|MockObject
     */
    private $totalsItemFactoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->configurationPoolMock = $this->createMock(ConfigurationPool::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->totalsItemFactoryMock = $this->createPartialMock(
            TotalsItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ItemConverter::class,
            [
                'configurationPool' => $this->configurationPoolMock,
                'eventManager' => $this->eventManagerMock,
                'totalsItemFactory' => $this->totalsItemFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testModelToDataObject()
    {
        $productType = 'simple';

        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('toArray')->willReturn(['options' => []]);
        $itemMock->method('getProductType')->willReturn($productType);

        $simpleConfigMock = $this->createMock(Configuration::class);
        $defaultConfigMock = $this->createMock(Configuration::class);

        $this->configurationPoolMock->method('getByProductType')
            ->willReturnMap([['simple', $simpleConfigMock], ['default', $defaultConfigMock]]);

        $options = ['1' => ['label' => 'option1'], '2' => ['label' => 'option2']];
        $simpleConfigMock->expects($this->once())
            ->method('getOptions')
            ->with($itemMock)
            ->willReturn($options);

        $option = ['data' => 'optionsData', 'label' => ''];
        $defaultConfigMock->method('getFormattedOptionValue')->willReturn($option);

        $this->eventManagerMock->expects($this->once())->method('dispatch')
            ->with('items_additional_data', ['item' => $itemMock]);

        $this->totalsItemFactoryMock->expects($this->once())->method('create');

        $expectedData = [
            'options' => '{"1":{"data":"optionsData","label":"option1"},"2":{"data":"optionsData","label":"option2"}}'
        ];
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(null, $expectedData, TotalsItemInterface::class);

        $optionData = [
            '1' => [
                'data' => 'optionsData',
                'label' => 'option1'
            ],
            '2' => [
                'data' => 'optionsData',
                'label' => 'option2'
            ]
        ];

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn(json_encode($optionData));

        $this->model->modelToDataObject($itemMock);
    }
}
