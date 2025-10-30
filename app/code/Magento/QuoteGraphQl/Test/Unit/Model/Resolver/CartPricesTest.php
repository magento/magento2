<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\DataObjectHelper;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterfaceFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterface;
use Magento\Quote\Api\Data\TotalsExtension;
use Magento\Quote\Api\Data\AddressExtension;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\Resolver\CartPrices;
use GraphQL\Language\AST\OperationDefinitionNode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CartPrices
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartPricesTest extends TestCase
{
    /**
     * @var CartPrices
     */
    private CartPrices $cartPrices;

    /**
     * @var TotalsCollector|MockObject
     */
    private TotalsCollector $totalsCollectorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfigMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private DataObjectHelper $dataObjectHelperMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var Quote|MockObject
     */
    private Quote $quoteMock;

    /**
     * @var Total|MockObject
     */
    private Total $totalMock;

    /**
     * @var TotalsInterfaceFactory|MockObject
     */
    private TotalsInterfaceFactory $totalsFactoryMock;

    /**
     * @var TotalsExtensionInterfaceFactory|MockObject
     */
    private TotalsExtensionInterfaceFactory $totalExtensionFactoryMock;

    /**
     * @var TotalsExtension|MockObject
     */
    private TotalsExtension $totalExtensionMock;

    /**
     * @var Address|MockObject
     */
    private Address $shippingAddressMock;

    /**
     * @var AddressExtension|MockObject
     */
    private AddressExtension $addressExtensionMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->totalsFactoryMock = $this->getMockBuilder(TotalsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->addMethods(
                [
                    'getSubtotal',
                    'getSubtotalInclTax',
                    'getGrandTotal',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount',
                    'getDiscountDescription',
                    'getAppliedTaxes'
                ]
            )
            ->getMock();
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->totalExtensionFactoryMock = $this->createMock(TotalsExtensionInterfaceFactory::class);
        $this->totalExtensionMock = $this->createMock(TotalsExtension::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->resolveInfoMock->operation = new OperationDefinitionNode([]);
        $this->contextMock = $this->createMock(Context::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteCurrencyCode'])
            ->onlyMethods(['isVirtual', 'getShippingAddress'])
            ->getMock();
        $this->totalMock = $this->getMockBuilder(Total::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getSubtotal',
                    'getSubtotalInclTax',
                    'getGrandTotal',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount',
                    'getDiscountDescription',
                    'getAppliedTaxes',
                    'setExtensionAttributes'
                ]
            )
            ->getMock();

        $this->cartPrices = new CartPrices(
            $this->totalsCollectorMock,
            $this->scopeConfigMock,
            $this->totalsFactoryMock,
            $this->dataObjectHelperMock,
            $this->totalExtensionFactoryMock
        );
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->cartPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolveQuery(): void
    {
        $this->resolveInfoMock->operation->operation = 'query';
        $extAttributes = ['custom_field' => 'custom_value'];

        $this->addressExtensionMock = $this->createMock(AddressExtension::class);

        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getExtensionAttributes'])
            ->getMock();

        $this->shippingAddressMock->expects($this->any())
            ->method('getData')
            ->willReturn([]);

        $this->shippingAddressMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->addressExtensionMock);

        $this->addressExtensionMock
            ->expects($this->once())
            ->method('__toArray')
            ->willReturn($extAttributes);

        $this->quoteMock
            ->expects($this->once())
            ->method('isVirtual')
            ->willReturn(0);

        $this->quoteMock
            ->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->totalsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->totalMock);

        $this->totalExtensionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->totalExtensionMock);

        $this->totalMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->totalExtensionMock);

        $this->dataObjectHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->withConsecutive(
                [
                    $this->identicalTo($this->totalMock),
                    [],
                    $this->equalTo(TotalsInterface::class)
                ],
                [
                    $this->identicalTo($this->totalExtensionMock),
                    $this->equalTo($extAttributes),
                    $this->equalTo(TotalsExtensionInterface::class)
                ]
            );


        $this->resolve();
    }

    public function testResolveQueryVirtual(): void
    {
        $this->quoteMock
            ->expects($this->once())
            ->method('isVirtual')
            ->willReturn(1);

        $this->totalMock
            ->expects($this->once())
            ->method('getAppliedTaxes');

        $this->resolve();
    }
    public function testResolveMutation(): void
    {
        $this->resolveInfoMock->operation->operation = 'mutation';

        $this->totalMock
            ->expects($this->once())
            ->method('getAppliedTaxes');

        $this->resolve();
    }

    private function resolve(): void
    {
        $this->valueMock = ['model' => $this->quoteMock];
        $this->quoteMock
            ->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn('USD');
        $this->totalMock
            ->expects($this->once())
            ->method('getGrandTotal');
        $this->totalMock
            ->expects($this->exactly(2))
            ->method('getSubtotal');
        $this->totalMock
            ->expects($this->once())
            ->method('getSubtotalInclTax');
        $this->totalMock
            ->method('getDiscountDescription')
            ->willReturn('Discount Description');
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->totalMock
            ->expects($this->atLeast(2))
            ->method('getDiscountAmount');
        $this->totalMock
            ->expects($this->once())
            ->method('getDiscountTaxCompensationAmount');
        $this->totalsCollectorMock
            ->method('collectQuoteTotals')
            ->willReturn($this->totalMock);
        $this->cartPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }
}
