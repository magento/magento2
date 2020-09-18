<?php
/**
 *
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
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemConverterTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $configPoolMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $totalsFactoryMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var ItemConverter
     */
    private $model;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->configPoolMock = $this->createMock(ConfigurationPool::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->totalsFactoryMock = $this->createPartialMock(
            TotalsItemInterfaceFactory::class,
            ['create']
        );

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $this->model = new ItemConverter(
            $this->configPoolMock,
            $this->eventManagerMock,
            $this->totalsFactoryMock,
            $this->dataObjectHelperMock,
            $this->serializerMock
        );
    }

    public function testModelToDataObject()
    {
        $productType = 'simple';

        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('toArray')->willReturn(['options' => []]);
        $itemMock->expects($this->any())->method('getProductType')->willReturn($productType);

        $simpleConfigMock = $this->createMock(Configuration::class);
        $defaultConfigMock = $this->createMock(Configuration::class);

        $this->configPoolMock->expects($this->any())->method('getByProductType')
            ->willReturnMap([['simple', $simpleConfigMock], ['default', $defaultConfigMock]]);

        $options = ['1' => ['label' => 'option1'], '2' => ['label' => 'option2']];
        $simpleConfigMock->expects($this->once())->method('getOptions')->with($itemMock)
            ->willReturn($options);

        $option = ['data' => 'optionsData', 'label' => ''];
        $defaultConfigMock->expects($this->any())->method('getFormattedOptionValue')->willReturn($option);

        $this->eventManagerMock->expects($this->once())->method('dispatch')
            ->with('items_additional_data', ['item' => $itemMock]);

        $this->totalsFactoryMock->expects($this->once())->method('create');

        $expectedData = [
            'options' => '{"1":{"data":"optionsData","label":"option1"},"2":{"data":"optionsData","label":"option2"}}'
        ];
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray')
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
        $this->serializerMock->expects($this->once())->method('serialize')
            ->willReturn(json_encode($optionData));

        $this->model->modelToDataObject($itemMock);
    }
}
