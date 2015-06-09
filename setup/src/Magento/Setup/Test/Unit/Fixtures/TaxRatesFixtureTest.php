<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\TaxRatesFixture;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax;
use Test\AAaa\test;

class TaxRatesFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testExecute()
    {
        $rateMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rate')
            ->disableOriginalConstructor()
            ->setMethods([
                'setId',
                'delete'
            ])
            ->getMock();

        $collectionMock = $this->getMockBuilder('Magento\Tax\Model\Resource\Calculation\Rate\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $csvImportHandlerMock = $this->getMockBuilder('Magento\TaxImportExport\Model\Rate\CsvImportHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($collectionMock, $rateMock));
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($csvImportHandlerMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('taxRates.file');
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $taxRatesFixture = new TaxRatesFixture($this->fixtureModelMock);
        $taxRatesFixture->execute();
    }

    public function testGetActionTitle()
    {
        $taxRatesFixture = new TaxRatesFixture($this->fixtureModelMock);
        $this->assertSame('Generating tax rates', $taxRatesFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $taxRatesFixture = new TaxRatesFixture($this->fixtureModelMock);
        $this->assertSame([], $taxRatesFixture->introduceParamLabels());
    }
}
