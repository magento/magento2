<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Setup;

class TaxSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Setup\TaxSetup
     */
    protected $taxSetup;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeConfigMock;

    protected function setUp(): void
    {
        $this->typeConfigMock = $this->createMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);

        $salesSetup = $this->createMock(\Magento\Sales\Setup\SalesSetup::class);
        $salesSetupFactory = $this->createPartialMock(\Magento\Sales\Setup\SalesSetupFactory::class, ['create']);
        $salesSetupFactory->expects($this->any())->method('create')->willReturn($salesSetup);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxSetup = $helper->getObject(
            \Magento\Tax\Setup\TaxSetup::class,
            [
                'productTypeConfig' => $this->typeConfigMock,
                'salesSetupFactory' => $salesSetupFactory,
            ]
        );
    }

    public function testGetTaxableItems()
    {
        $refundable = ['simple', 'simple2'];
        $this->typeConfigMock->expects(
            $this->once()
        )->method(
            'filter'
        )->with(
            'taxable'
        )->willReturn(
            $refundable
        );
        $this->assertEquals($refundable, $this->taxSetup->getTaxableItems());
    }
}
