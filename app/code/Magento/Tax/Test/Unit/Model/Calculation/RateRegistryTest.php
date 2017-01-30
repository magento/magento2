<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for RateRegistry
 *
 */
class RateRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Calculation\RateRegistry
     */
    private $rateRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\RateFactory
     */
    private $rateModelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\Rate
     */
    private $rateModelMock;

    const TAX_RATE_ID = 1;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->rateModelFactoryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\RateFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateRegistry = $objectManager->getObject(
            'Magento\Tax\Model\Calculation\RateRegistry',
            ['taxModelRateFactory' => $this->rateModelFactoryMock]
        );
        $this->rateModelMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rate')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRegisterTaxRate()
    {
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAX_RATE_ID));
        $this->rateRegistry->registerTaxRate($this->rateModelMock);
        $this->assertEquals($this->rateModelMock, $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID));
    }

    public function testRetrieveTaxRate()
    {
        $this->rateModelMock->expects($this->once())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->will($this->returnValue($this->rateModelMock));
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAX_RATE_ID));
        $this->rateModelFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->rateModelMock));

        $actual = $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
        $this->assertEquals($this->rateModelMock, $actual);

        $actualCached = $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
        $this->assertSame($actual, $actualCached);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $this->rateModelMock->expects($this->once())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->will($this->returnValue($this->rateModelMock));
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->rateModelFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->rateModelMock));
        $this->rateRegistry->retrieveTaxRate(self::TAX_RATE_ID);
    }

    public function testRemoveTaxRate()
    {
        $this->rateModelMock->expects($this->any())
            ->method('load')
            ->with(self::TAX_RATE_ID)
            ->will($this->returnValue($this->rateModelMock));

        // The second time this is called, want it to return null indicating a new object
        $this->rateModelMock->expects($this->any())
            ->method('getId')
            ->will($this->onConsecutiveCalls(self::TAX_RATE_ID, null));

        $this->rateModelFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->rateModelMock));

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
