<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart\Totals;

class ItemConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configPoolMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $totalsFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Quote\Model\Cart\Totals\ItemConverter
     */
    private $model;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->configPoolMock = $this->createMock(\Magento\Catalog\Helper\Product\ConfigurationPool::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->totalsFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\TotalsItemInterfaceFactory::class,
            ['create']
        );

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)->getMock();

        $this->model = new \Magento\Quote\Model\Cart\Totals\ItemConverter(
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

        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $itemMock->expects($this->once())->method('toArray')->willReturn(['options' => []]);
        $itemMock->expects($this->any())->method('getProductType')->willReturn($productType);

        $simpleConfigMock = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
        $defaultConfigMock = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);

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
            ->with(null, $expectedData, \Magento\Quote\Api\Data\TotalsItemInterface::class);

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
