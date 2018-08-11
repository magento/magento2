<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\AlreadyExistsException;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation\RateRepository;

/**
 * Class RateRepositoryTest
 * @package Magento\Tax\Test\Unit\Model\Calculation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RateRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RateRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $countryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $regionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $joinProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->rateConverterMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate\Converter::class);
        $this->rateRegistryMock = $this->createMock(\Magento\Tax\Model\Calculation\RateRegistry::class);
        $this->searchResultFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResultMock = $this->createMock(\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class);
        $this->rateFactoryMock = $this->createPartialMock(
            \Magento\Tax\Model\Calculation\RateFactory::class,
            ['create']
        );
        $this->countryFactoryMock = $this->createPartialMock(
            \Magento\Directory\Model\CountryFactory::class,
            ['create']
        );
        $this->regionFactoryMock = $this->createPartialMock(\Magento\Directory\Model\RegionFactory::class, ['create']);
        $this->rateResourceMock = $this->createMock(\Magento\Tax\Model\ResourceModel\Calculation\Rate::class);
        $this->joinProcessorMock = $this->createMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        );
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->model = new RateRepository(
            $this->rateConverterMock,
            $this->rateRegistryMock,
            $this->searchResultFactory,
            $this->rateFactoryMock,
            $this->countryFactoryMock,
            $this->regionFactoryMock,
            $this->rateResourceMock,
            $this->joinProcessorMock,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $countryCode = 'US';
        $countryMock = $this->createMock(\Magento\Directory\Model\Country::class);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateTitles = [
            'Label 1',
            'Label 2',
        ];
        $rateMock = $this->getTaxRateMock([
            'id' => null,
            'tax_country_id' => $countryCode,
            'tax_region_id' => $regionId,
            'region_name' => null,
            'tax_postcode' => null,
            'zip_is_range' => true,
            'zip_from' => 90000,
            'zip_to' => 90005,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ]);
        $this->rateConverterMock->expects($this->once())->method('createTitleArrayFromServiceObject')
            ->with($rateMock)->will($this->returnValue($rateTitles));
        $this->rateResourceMock->expects($this->once())->method('save')->with($rateMock);
        $rateMock->expects($this->once())->method('saveTitles')->with($rateTitles);
        $this->rateRegistryMock->expects($this->once())->method('registerTaxRate')->with($rateMock);

        $this->model->save($rateMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No such entity with id 9999
     */
    public function testSaveThrowsExceptionIfTargetTaxRateDoesNotExist()
    {
        $rateTitles = [
            'Label 1',
            'Label 2',
        ];
        $rateId = 9999;
        $rateMock = $this->getTaxRateMock([
            'id' => $rateId,
            'tax_country_id' => 'US',
            'tax_region_id' => 1,
            'region_name' => null,
            'tax_postcode' => null,
            'zip_is_range' => true,
            'zip_from' => 90000,
            'zip_to' => 90005,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ]);
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId)
            ->willThrowException(new \Exception('No such entity with id ' . $rateId));
        $this->rateResourceMock->expects($this->never())->method('save')->with($rateMock);
        $this->rateRegistryMock->expects($this->never())->method('registerTaxRate')->with($rateMock);

        $this->model->save($rateMock);
    }

    public function testGet()
    {
        $rateId = 1;
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId);
        $this->model->get($rateId);
    }

    public function testDelete()
    {
        $rateMock = $this->getTaxRateMock(['id' => 1]);
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->delete($rateMock);
    }

    public function testDeleteById()
    {
        $rateId = 1;
        $rateMock = $this->getTaxRateMock(['id' => $rateId]);
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId)
            ->will($this->returnValue($rateMock));
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->deleteById($rateId);
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $rateMock = $this->getTaxRateMock([]);

        $objectManager = new ObjectManager($this);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class,
            $items
        );
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->will($this->returnValue(count($items)));

        $this->rateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateMock));
        $rateMock->expects($this->any())->method('getCollection')->will($this->returnValue($collectionMock));

        $this->searchResultMock->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);

        $this->joinProcessorMock->expects($this->once())->method('process')->with($collectionMock);

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * Retrieve tax rate mock
     *
     * @param array $taxRateData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getTaxRateMock(array $taxRateData)
    {
        $taxRateMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate::class);
        foreach ($taxRateData as $key => $value) {
            // convert key from snake case to upper case
            $taxRateMock->expects($this->any())
                ->method('get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                ->will($this->returnValue($value));
        }

        return $taxRateMock;
    }

    /**
     * @dataProvider saveThrowsExceptionIfCannotSaveTitlesDataProvider
     * @param LocalizedException $expectedException
     * @param string $exceptionType
     * @param string $exceptionMessage
     * @throws LocalizedException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testSaveThrowsExceptionIfCannotSaveTitles($expectedException, $exceptionType, $exceptionMessage)
    {
        $countryCode = 'US';
        $countryMock = $this->createMock(\Magento\Directory\Model\Country::class);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateTitles = ['Label 1', 'Label 2'];
        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => $countryCode,
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => 90000,
                'zip_to' => 90005,
                'rate' => 7.5,
                'code' => 'Tax Rate Code',
                'titles' => $rateTitles,
            ]
        );
        $this->rateConverterMock->expects($this->once())->method('createTitleArrayFromServiceObject')
            ->with($rateMock)->will($this->returnValue($rateTitles));
        $this->rateResourceMock->expects($this->once())->method('save')->with($rateMock);
        $rateMock
            ->expects($this->once())
            ->method('saveTitles')
            ->with($rateTitles)
            ->willThrowException($expectedException);
        $this->rateRegistryMock->expects($this->never())->method('registerTaxRate')->with($rateMock);
        $this->expectException($exceptionType);
        $this->expectExceptionMessage($exceptionMessage);
        $this->model->save($rateMock);
    }

    /**
     * @return array
     */
    public function saveThrowsExceptionIfCannotSaveTitlesDataProvider()
    {
        return [
            'entity_already_exists' => [
                new AlreadyExistsException(__('Entity already exists')),
                AlreadyExistsException::class,
                'Entity already exists'
            ],
            'cannot_save_title' => [
                new LocalizedException(__('Cannot save titles')),
                LocalizedException::class,
                'Cannot save titles'
            ]
        ];
    }

    public function testGetListWhenFilterGroupExists()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $objectManager = new ObjectManager($this);
        $rateMock = $this->getTaxRateMock([]);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class,
            $items
        );
        $rateMock = $this->getTaxRateMock([]);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->will($this->returnValue(count($items)));

        $this->rateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateMock));
        $rateMock->expects($this->any())->method('getCollection')->will($this->returnValue($collectionMock));

        $this->searchResultMock->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);

        $this->joinProcessorMock->expects($this->once())->method('process')->with($collectionMock);

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred.
     */
    public function testValidate()
    {
        $regionId = 2;
        $rateTitles = ['Label 1', 'Label 2'];
        $regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue(''));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));
        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => '',
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => -90000,
                'zip_to' => '',
                'rate' => '',
                'code' => '',
                'titles' => $rateTitles,
            ]
        );
        $this->model->save($rateMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage percentage_rate is a required field.
     */
    public function testValidateWithNoRate()
    {
        $rateTitles = ['Label 1', 'Label 2'];

        $countryCode = 'US';
        $countryMock = $this->createMock(\Magento\Directory\Model\Country::class);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => $countryCode,
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => 90000,
                'zip_to' => 90005,
                'rate' => '',
                'code' => 'Tax Rate Code',
                'titles' => $rateTitles,
            ]
        );
        $this->model->save($rateMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage percentage_rate is a required field.
     */
    public function testValidateWithWrongRate()
    {
        $rateTitles = ['Label 1', 'Label 2'];

        $countryCode = 'US';
        $countryMock = $this->createMock(\Magento\Directory\Model\Country::class);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateMock = $this->getTaxRateMock(
            [
                'id' => null,
                'tax_country_id' => $countryCode,
                'tax_region_id' => $regionId,
                'region_name' => null,
                'tax_postcode' => null,
                'zip_is_range' => true,
                'zip_from' => 90000,
                'zip_to' => 90005,
                'rate' => '7,9',
                'code' => 'Tax Rate Code',
                'titles' => $rateTitles,
            ]
        );
        $this->model->save($rateMock);
    }
}
