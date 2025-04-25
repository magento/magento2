<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Test\Unit\Model\Resolver;

use Magento\CatalogCustomerGraphQl\Model\Resolver\Customer\GetCustomerGroup;
use Magento\CatalogCustomerGraphQl\Model\Resolver\PriceTiers;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\Tiers;
use Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price\TiersFactory;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test Resolver for PriceTiers
 */
class PriceTiersTest extends TestCase
{
    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var TiersFactory|MockObject
     */
    private $tiersFactory;

    /**
     * @var PriceTiers
     */
    private $priceTiers;

    protected function setUp(): void
    {
        $valueFactory = $this->createMock(ValueFactory::class);
        $this->tiersFactory = $this->createMock(TiersFactory::class);
        $customerGroup = $this->createMock(GetCustomerGroup::class);
        $priceDiscount = $this->createMock(Discount::class);
        $providerPool = $this->createMock(ProviderPool::class);
        $priceCurrency = $this->createMock(PriceCurrency::class);
        $this->priceTiers = new PriceTiers(
            $valueFactory,
            $this->tiersFactory,
            $customerGroup,
            $priceDiscount,
            $providerPool,
            $priceCurrency
        );

        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
    }

    public function testResolve()
    {
        $tiers = $this->createMock(Tiers::class);
        $tiers->expects($this->once())
            ->method('addProductFilter')
            ->willReturnSelf();

        $this->tiersFactory->expects($this->once())
            ->method('create')
            ->willReturn($tiers);

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->never())
            ->method('getTierPrices');

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $valueMock = ['model' => $productMock];

        $this->priceTiers->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $valueMock);
    }
}
