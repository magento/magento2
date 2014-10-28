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
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRateServiceTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */

    const REGION_ID = 42;

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

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | RateFactory
     */
    private $rateFactoryMock;

    /**
     * @var Data\TaxRateSearchResultsBuilder
     */
    private $taxRateSearchResultsBuilder;

    /**
     * @var  \Magento\Tax\Service\V1\Data\TaxRateBuilder'
     */
    private $taxRateBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Directory\Model\CountryFactory
     */
    private $countryFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Directory\Model\RegionFactory
     */
    private $regionFactoryMock;

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
        $this->rateFactoryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\RateFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxRateSearchResultsBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateSearchResultsBuilder'
        );
        $filterGroupBuilder = $this->objectManager
            ->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var SearchCriteriaBuilder $searchBuilder */
        $this->searchCriteriaBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );

        $this->countryFactoryMock = $this->getMockBuilder('Magento\Directory\Model\CountryFactory')
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $countryMock = $this->getMockBuilder('Magento\Directory\Model\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $countryMock->expects($this->any())->method('loadByCode')->will($this->returnValue($countryMock));
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue('valid'));
        $this->countryFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($countryMock));

        $this->regionFactoryMock = $this->getMockBuilder('Magento\Directory\Model\RegionFactory')
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $regionMock = $this->getMockBuilder('Magento\Directory\Model\Region')
            ->disableOriginalConstructor()
            ->getMock();
        $regionMock->expects($this->any())->method('load')->will($this->returnValue($regionMock));
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue('valid'));
        $this->regionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($regionMock));

        $zipRangeBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $this->taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder',
            ['zipRangeBuilder' => $zipRangeBuilder]
        );
        $this->createService();
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

        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->rateModelMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->rateModelMock));
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->will($this->returnValue($this->rateModelMock));
        $taxRate = $this->taxRateBuilder->populate($taxRateDataObject)->setPostcode('78765-78780')->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateDataObjectFromModel')
            ->will($this->returnValue($taxRate));

        $taxRateServiceData = $this->taxRateService->createTaxRate($taxRateDataObject);

        //Assertion
        $this->assertSame($taxRate, $taxRateServiceData);

    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage id is not expected for this request.
     */
    public function testCreateTaxRateWithId()
    {
        $taxData = [
            'id' => 2,
            'country_id' => '',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];
        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
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
        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "XX" provided for the country_id field.
     */
    public function testCreateTaxRateWithInputException_invalidCountry()
    {
        $taxData = [
            'country_id' => 'XX',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];

        // create mock country object with invalid country
        $this->countryFactoryMock = $this->getMockBuilder('Magento\Directory\Model\CountryFactory')
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $countryMock = $this->getMockBuilder('Magento\Directory\Model\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $countryMock->expects($this->any())
            ->method('loadByCode')
            ->will($this->returnValue($countryMock));
        $countryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->countryFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($countryMock));

        // recreate the service with new countryMock values
        $this->createService();

        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage country_id is a required field.
     */
    public function testCreateTaxRateWithInputException_spaceCountry()
    {
        $taxData = [
            'country_id' => ' ',
            'region_id' => '8',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];

        // create mock country object with invalid country
        $this->countryFactoryMock = $this->getMockBuilder('Magento\Directory\Model\CountryFactory')
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $countryMock = $this->getMockBuilder('Magento\Directory\Model\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $countryMock->expects($this->any())->method('loadByCode')->will($this->returnValue($countryMock));
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(null));

        // recreate the service with new countryMock values
        $this->createService();

        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "-" provided for the region_id field.
     */
    public function testCreateTaxRateWithInputException_invalidRegion()
    {
        $taxData = [
            'country_id' => 'US',
            'region_id' => '-',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];

        // create mock country object with invalid region
        $this->regionFactoryMock = $this->getMockBuilder('Magento\Directory\Model\RegionFactory')
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $regionMock = $this->getMockBuilder('Magento\Directory\Model\Region')
            ->disableOriginalConstructor()
            ->getMock();
        $regionMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($regionMock));
        $regionMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->regionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($regionMock));

        // recreate the service with new regionMock values
        $this->createService();

        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->taxRateService->createTaxRate($taxRateDataObject);
    }

    public function testCreateTaxRateWithInputException_spaceRegion()
    {
        $taxData = [
            'country_id' => 'US',
            'region_id' => ' ',
            'percentage_rate' => '8.25',
            'code' => 'US-CA-*-Rate',
            'zip_range' => ['from' => 78765, 'to' => 78780]
        ];

        $taxRateDataObject =   $this->taxRateBuilder->populateWithArray($taxData)->create();
        $this->rateModelMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->rateModelMock));
        $this->converterMock->expects($this->once())
            ->method('createTaxRateModel')
            ->will($this->returnValue($this->rateModelMock));
        $taxRate =   $this->taxRateBuilder->populate($taxRateDataObject)->setPostcode('78765-78780')->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateDataObjectFromModel')
            ->will($this->returnValue($taxRate));

        $taxRateServiceData = $this->taxRateService->createTaxRate($taxRateDataObject);

        //Assertion
        $this->assertSame($taxRate, $taxRateServiceData);
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

        $taxRateDataObject = $this->taxRateBuilder->populateWithArray($taxData)->create();
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
        $taxRate = $this->taxRateBuilder
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
        $taxRate = $this->taxRateBuilder
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
        $taxRate = $this->taxRateBuilder
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

    public function testSearchTaxRateEmpty()
    {
        $collectionMock = $this->getMockBuilder('Magento\Tax\Model\Resource\Calculation\Rate\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'load', 'joinRegionTable'])
            ->getMock();

        $this->mockReturnValue($collectionMock, ['getSize' => 0]);
        $this->rateFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->rateModelMock));
        $this->mockReturnValue(
            $this->rateModelMock,
            [
                'load' => $this->returnSelf(),
                'getCollection' => $collectionMock
            ]
        );

        $filterBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField(TaxRate::KEY_REGION_ID)->setValue(self::REGION_ID)->create();
        $this->searchCriteriaBuilder->addFilter([$filter]);

        $this->createService();
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->getSortOrders();
        $searchResults = $this->taxRateService->searchTaxRates($searchCriteria);
        $items = $searchResults->getItems();
        $this->assertNotNull($searchResults);
        $this->assertSame($searchCriteria, $searchResults->getSearchCriteria());
        $this->assertEquals(0, $searchResults->getTotalCount());
        $this->assertNotNull($items);
        $this->assertTrue(empty($items));
    }

    public function testSearchTaxRate()
    {
        $collectionMock = $this->getMockBuilder('Magento\Tax\Model\Resource\Calculation\Rate\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'load', 'getIterator', 'joinRegionTable'])
            ->getMock();

        $this->mockReturnValue(
            $collectionMock,
            [
                'getSize' => 1,
                'getIterator' => new \ArrayIterator([$this->rateModelMock])
            ]
        );

        $this->rateFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->rateModelMock));

        $this->mockReturnValue(
            $this->rateModelMock,
            [
                'load' => $this->returnSelf(),
                'getCollection' => $collectionMock,
            ]
        );

        $taxRate = $this->taxRateBuilder
            ->setId(2)
            ->setCode('Rate-Code')
            ->setCountryId('US')
            ->setPercentageRate(0.1)
            ->setPostcode('55555')
            ->setRegionId(self::REGION_ID)
            ->create();
        $this->converterMock->expects($this->once())
            ->method('createTaxRateDataObjectFromModel')
            ->with($this->rateModelMock)
            ->will($this->returnValue($taxRate));

        $filterBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField(TaxRate::KEY_REGION_ID)->setValue(self::REGION_ID)->create();
        $sortOrderBuilder = $this->objectManager->getObject('\Magento\Framework\Service\V1\Data\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField(TaxRate::KEY_REGION_ID)
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        $this->searchCriteriaBuilder
            ->addFilter([$filter])
            ->addSortOrder($sortOrder);
        $this->createService();
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->taxRateService->searchTaxRates($searchCriteria);
        $items = $searchResults->getItems();
        $this->assertNotNull($searchResults);
        $this->assertSame($searchCriteria, $searchResults->getSearchCriteria());
        $this->assertEquals(1, $searchResults->getTotalCount());
        $this->assertNotNull($items);
        $this->assertFalse(empty($items));
        $this->assertEquals(self::REGION_ID, $items[0]->getRegionId());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }

    /**
     * create taxRateService
     */
    private function createService()
    {
        $this->taxRateService = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\TaxRateService',
            [
                'rateFactory' => $this->rateFactoryMock,
                'rateRegistry' => $this->rateRegistryMock,
                'converter' => $this->converterMock,
                'taxRateSearchResultsBuilder' => $this->taxRateSearchResultsBuilder,
                'countryFactory' => $this->countryFactoryMock,
                'regionFactory' => $this->regionFactoryMock
            ]
        );
    }
}
