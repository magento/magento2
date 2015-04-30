<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CartTotalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationPoolMock;

    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->totalsFactoryMock = $this->getMock(
            'Magento\Quote\Api\Data\TotalsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationPoolMock = $this->getMock(
            '\Magento\Catalog\Helper\Product\ConfigurationPool',
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Quote\Model\Cart\CartTotalRepository',
            [
                'totalsFactory' => $this->totalsFactoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'configurationPool' => $this->configurationPoolMock,
            ]
        );
    }

    public function testGet()
    {
        $cartId = 12;
        $itemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            ['setWeeeTaxApplied', 'getWeeeTaxApplied', 'toArray', 'getProductType'],
            [],
            '',
            false
        );
        $itemToArray = ['name' => 'item'];
        $visibleItems = [
            11 => $itemMock,
        ];
        $configMock1 = $this->getMock('\Magento\Catalog\Helper\Product\Configuration', [], [], '', false);
        $configMock2 = $this->getMock('\Magento\Catalog\Helper\Product\Configuration', [], [], '', false);
        $typesMap = [['simple', $configMock1], ['default', $configMock2]];

        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->addressMock);
        $this->addressMock->expects($this->once())->method('getData')->willReturn(['addressData']);
        $this->quoteMock->expects($this->once())->method('getData')->willReturn(['quoteData']);

        $this->quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn($visibleItems);
        $itemMock->expects($this->once())->method('setWeeeTaxApplied')->with([1, 2, 3]);
        $itemMock->expects($this->once())->method('getWeeeTaxApplied')->willReturn(serialize([1, 2, 3]));
        $itemMock->expects($this->once())->method('toArray')->willReturn($itemToArray);

        $totalsMock = $this->getMock('Magento\Quote\Model\Cart\Totals', ['setItems'], [], '', false);
        $this->totalsFactoryMock->expects($this->once())->method('create')->willReturn($totalsMock);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        //expectations of method getFormattedOptionsValue()
        $itemMock->expects($this->any())->method('getProductType')->willReturn('simple');
        $this->configurationPoolMock->expects($this->atLeastOnce())
            ->method('getByProductType')
            ->willReturnMap($typesMap);
        $configMock1->expects($this->once())->method('getOptions')->willReturn([4 => ['label' => 'justLabel']]);
        $configMock2->expects($this->once())->method('getFormattedOptionValue');
        //back in get()
        $totalsMock->expects($this->once())->method('setItems')->with(
            [
            11 => [
                    'name' => 'item',
                    'options' => [ 4 => ['label' => 'justLabel']],
                ],
            ]
        );

        $this->assertEquals($totalsMock, $this->model->get($cartId));
    }
}
