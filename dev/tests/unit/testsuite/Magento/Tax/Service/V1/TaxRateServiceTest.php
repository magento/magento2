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
namespace Magento\Tax\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rate as RateModel;
use Magento\Tax\Service\V1\Data\TaxRate;
use Magento\TestFramework\Helper\ObjectManager;

class TaxRateServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxRateServiceInterface
     */
    private $taxRateService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\RateRegistry
     */
    private $rateRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\Rate\Converter
     */
    private $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\Calculation\Rate
     */
    private $rateModelMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->rateRegistryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\RateRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rate\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateModelMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rate')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRateService = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\TaxRateService',
            [
                'rateRegistry' => $this->rateRegistryMock,
                'converter' => $this->converterMock,
            ]
        );
    }

    public function testCreateTaxRate()
    {
        $taxData = [
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];

        $zipRangeBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder',
            ['zipRangeBuilder' => $zipRangeBuilder]
        );

        $taxRateDataObject = $taxRateBuilder->populateWithArray($taxData)->create();
        $this->rateModelMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->rateModelMock));
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->will($this->returnValue($this->rateModelMock));
        $taxRate = $taxRateBuilder->populate($taxRateDataObject)->setPostcode('78765-78780')->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateDataObjectFromModel')
            ->will($this->returnValue($taxRate));

        $taxRateServiceData = $this->taxRateService->createTaxRate($taxRateDataObject);

        //Assertion
        $this->assertSame($taxRate, $taxRateServiceData);

    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage country_id is a required field.
     */
    public function testCreateTaxRateWithInputException()
    {
        $taxData = [
            'country_id' => '',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        $zipRangeBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder',
            ['zipRangeBuilder' => $zipRangeBuilder]
        );
        $taxRateDataObject = $taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testCreateTaxRateWithModelException()
    {
        $taxData = [
            'country_id' => 'US',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        $zipRangeBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder',
            ['zipRangeBuilder' => $zipRangeBuilder]
        );
        $taxRateDataObject = $taxRateBuilder->populateWithArray($taxData)->create();
        $this->rateModelMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Magento\Framework\Model\Exception()));
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->will($this->returnValue($this->rateModelMock));
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    public function testGetTaxRate()
    {
        $taxRateDataObjectMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxRate')
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateRegistryMock->expects($this->once())
            ->method('retrieveTaxRate')
            ->with(1)
            ->will($this->returnValue($this->rateModelMock));
        $this->converterMock->expects($this->once())
            ->method('createTaxRateDataObjectFromModel')
            ->with($this->rateModelMock)
            ->will($this->returnValue($taxRateDataObjectMock));
        $this->assertEquals($taxRateDataObjectMock, $this->taxRateService->getTaxRate(1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with taxRateId = 1
     */
    public function testGetTaxRateWithNoSuchEntityException()
    {
        $rateId = 1;
        $this->rateRegistryMock->expects($this->once())
            ->method('retrieveTaxRate')
            ->with($rateId)
            ->will($this->throwException(NoSuchEntityException::singleField('taxRateId', $rateId)));
        $this->converterMock->expects($this->never())
            ->method('createTaxRateDataObjectFromModel');
        $this->taxRateService->getTaxRate($rateId);
    }

    public function testUpdateTaxRate()
    {
        $taxRateBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $taxRate = $taxRateBuilder
            ->setId(2)
            ->setCode('Rate-Code')
            ->setCountryId('US')
            ->setPercentageRate(0.1)
            ->setPostcode('55555')
            ->setRegionId('TX')
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->with($taxRate)
            ->will($this->returnValue($this->rateModelMock));
        $this->rateModelMock->expects($this->once())->method('save');

        $result = $this->taxRateService->updateTaxRate($taxRate);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateTaxRateNoId()
    {
        $taxRateBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $taxRate = $taxRateBuilder
            ->setCode('Rate-Code')
            ->setCountryId('US')
            ->setPercentageRate(0.1)
            ->setPostcode('55555')
            ->setRegionId('TX')
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->with($taxRate)
            ->will($this->throwException(new NoSuchEntityException()));

        $this->taxRateService->updateTaxRate($taxRate);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testUpdateTaxRateMissingRequiredInfo()
    {
        $taxRateBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $taxRate = $taxRateBuilder
            ->setId(2)
            ->setCode('Rate-Code')
            ->setCountryId('US')
            ->setPercentageRate(0.1)
            ->setRegionId('TX')
            ->create();

        $this->taxRateService->updateTaxRate($taxRate);
    }

    public function testDeleteTaxRate()
    {
        $this->rateRegistryMock->expects($this->once())
            ->method('retrieveTaxRate')
            ->with(1)
            ->will($this->returnValue($this->rateModelMock));
        $this->rateRegistryMock->expects($this->once())
            ->method('removeTaxRate')
            ->with(1)
            ->will($this->returnValue($this->rateModelMock));
        $this->taxRateService->deleteTaxRate(1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteTaxRateRetrieveException()
    {
        $this->rateRegistryMock->expects($this->once())
            ->method('retrieveTaxRate')
            ->with(1)
            ->will($this->throwException(new NoSuchEntityException()));
        $this->taxRateService->deleteTaxRate(1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Bad error occurred
     */
    public function testDeleteTaxRateDeleteException()
    {
        $this->rateRegistryMock->expects($this->once())
            ->method('retrieveTaxRate')
            ->with(1)
            ->will($this->returnValue($this->rateModelMock));
        $this->rateModelMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception('Bad error occurred')));
        $this->taxRateService->deleteTaxRate(1);
    }
}
