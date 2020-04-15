<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for RateRegistry
 *
 */
class RateRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Model\Calculation\RateRegistry
     */
    private $rateRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Tax\Model\Calculation\RateFactory
     */
    private $rateModelFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Tax\Model\Calculation\Rate
     */
    private $rateModelMock;

    const TAX_RATE_ID = 1;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->rateModelFactoryMock = $this->getMockBuilder(\Magento\Tax\Model\Calculation\RateFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateRegistry = $objectManager->getObject(
            \Magento\Tax\Model\Calculation\RateRegistry::class,
            ['taxModelRateFactory' => $this->rateModelFactoryMock]
        );
        $this->rateModelMock = $this->getMockBuilder(\Magento\Tax\Model\Calculation\Rate::class)
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

    /**
     */
    public function testRetrieveException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

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
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(self::TAX_RATE_ID, null));

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
