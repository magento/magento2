<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for RateRegistry
 *
 */
class RateRegistryTest extends TestCase
{
    /**
     * @var RateRegistry
     */
    private $rateRegistry;

    /**
     * @var MockObject|RateFactory
     */
    private $rateModelFactoryMock;

    /**
     * @var MockObject|Rate
     */
    private $rateModelMock;

    private const TAX_RATE_ID = 1;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->rateModelFactoryMock = $this->getMockBuilder(RateFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateRegistry = $objectManager->getObject(
            RateRegistry::class,
            ['taxModelRateFactory' => $this->rateModelFactoryMock]
        );
        $this->rateModelMock = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRegisterTaxRate()
    {
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::TAX_RATE_ID);
        $this->rateRegistry->registerTaxRate($this->rateModelMock);
        $this->assertEquals($this->rateModelMock, $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID));
    }

    public function testRetrieveTaxRate()
    {
        $this->rateModelMock->expects($this->once())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->willReturn($this->rateModelMock);
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::TAX_RATE_ID);
        $this->rateModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->rateModelMock);

        $actual = $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
        $this->assertEquals($this->rateModelMock, $actual);

        $actualCached = $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
        $this->assertSame($actual, $actualCached);
    }

    public function testRetrieveException()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->rateModelMock->expects($this->once())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->willReturn($this->rateModelMock);
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $this->rateModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->rateModelMock);
        $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
    }

    public function testRemoveTaxRate()
    {
        $this->rateModelMock->expects($this->any())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->willReturn($this->rateModelMock);

        // The second time this is called, want it to return null indicating a new object
        $callCount = 0;
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->willReturnCallback(function () use (&$callCount) {
                return $callCount++ === 0 ? self::TAX_RATE_ID : '';
            });

        $this->rateModelFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rateModelMock);

        $actual = $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
        $this->assertEquals($this->rateModelMock, $actual);

        // Remove the rate
        $this->rateRegistry->removeTaxRate(self::TAX_RATE_ID);

        // Verify that if the rate is retrieved again, an exception is thrown
        try {
            $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
            $this->fail('NoSuchEntityException was not thrown as expected');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'taxRateId',
                'fieldValue' => self::TAX_RATE_ID,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }
}
