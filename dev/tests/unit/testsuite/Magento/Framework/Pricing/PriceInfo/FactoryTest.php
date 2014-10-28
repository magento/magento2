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
namespace Magento\Framework\Pricing\PriceInfo;

use Magento\Framework\Pricing\PriceInfo\Factory;

/**
 * Test class for \Magento\Framework\Pricing\PriceInfo\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Factory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\Pricing\Price\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pricesMock;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * SetUp test
     */
    public function setUp()
    {
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            [],
            [],
            '',
            false
        );
        $this->pricesMock = $this->getMock(
            'Magento\Framework\Pricing\Price\Collection',
            [],
            [],
            '',
            false
        );
        $this->saleableItemMock = $this->getMockForAbstractClass(
            'Magento\Framework\Pricing\Object\SaleableInterface',
            [],
            '',
            false,
            true,
            true,
            ['getQty']
        );
        $this->priceInfoMock = $this->getMockForAbstractClass(
            'Magento\Framework\Pricing\PriceInfoInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->types = [
            'default' => [
                'infoClass' => 'Price\PriceInfo\Default',
                'prices' => 'Price\Collection\Default'
            ],
            'configurable' => [
                'infoClass' => 'Price\PriceInfo\Configurable',
                'prices' => 'Price\Collection\Configurable'
            ],
        ];
        $this->factory = new Factory($this->types, $this->objectManagerMock);
    }

    public function createPriceInfoDataProvider()
    {
        return [
            [
                'simple',
                1,
                'Price\PriceInfo\Default',
                'Price\Collection\Default'
            ],
            [
                'configurable',
                2,
                'Price\PriceInfo\Configurable',
                'Price\Collection\Configurable'
            ]
        ];
    }

    /**
     * @param $typeId
     * @param $quantity
     * @param $infoClass
     * @param $prices
     * @dataProvider createPriceInfoDataProvider
     */
    public function testCreate($typeId, $quantity, $infoClass, $prices)
    {
        $this->saleableItemMock->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeId));
        $this->saleableItemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($quantity));

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap(
                [
                    [
                        $prices,
                        [
                            'saleableItem' => $this->saleableItemMock,
                            'quantity' => $quantity
                        ],
                        $this->pricesMock
                    ],
                    [
                        $infoClass,
                        [
                            'saleableItem' => $this->saleableItemMock,
                            'quantity' => $quantity,
                            'prices' => $this->pricesMock
                        ],
                        $this->priceInfoMock
                    ]
                ]
            ));
        $this->assertEquals($this->priceInfoMock, $this->factory->create($this->saleableItemMock, []));
    }
}
