<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Setup;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Tax\Setup\TaxSetup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxSetupTest extends TestCase
{
    /**
     * @var TaxSetup
     */
    protected $taxSetup;

    /**
     * @var MockObject
     */
    protected $typeConfigMock;

    protected function setUp(): void
    {
        $this->typeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $salesSetup = $this->createMock(SalesSetup::class);
        $salesSetupFactory = $this->createPartialMock(SalesSetupFactory::class, ['create']);
        $salesSetupFactory->expects($this->any())->method('create')->willReturn($salesSetup);

        $helper = new ObjectManager($this);
        $this->taxSetup = $helper->getObject(
            TaxSetup::class,
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
