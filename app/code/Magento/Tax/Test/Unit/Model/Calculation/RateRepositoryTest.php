<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterface;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRegistry;
use Magento\Tax\Model\Calculation\RateRepository;
use Magento\Tax\Model\ResourceModel\Calculation\Rate;

use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RateRepositoryTest extends TestCase
{
    /**
     * @var RateRepository
     */
    private $model;

    /**
     * @var MockObject
     */
    private $rateConverterMock;

    /**
     * @var MockObject
     */
    private $rateRegistryMock;

    /**
     * @var MockObject
     */
    private $searchResultFactory;

    /**
     * @var MockObject
     */
    private $searchResultMock;

    /**
     * @var MockObject
     */
    private $rateFactoryMock;

    /**
     * @var MockObject
     */
    private $countryFactoryMock;

    /**
     * @var MockObject
     */
    private $regionFactoryMock;

    /**
     * @var MockObject
     */
    private $rateResourceMock;

    /**
     * @var MockObject
     */
    private $joinProcessorMock;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $this->rateConverterMock = $this->createMock(Converter::class);
        $this->rateRegistryMock = $this->createMock(RateRegistry::class);
        $this->searchResultFactory = $this->createPartialMock(
            TaxRuleSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResultMock = $this->getMockForAbstractClass(TaxRuleSearchResultsInterface::class);
        $this->rateFactoryMock = $this->createPartialMock(
            RateFactory::class,
            ['create']
        );
        $this->countryFactoryMock = $this->createPartialMock(
            CountryFactory::class,
            ['create']
        );
        $this->regionFactoryMock = $this->createPartialMock(RegionFactory::class, ['create']);
        $this->rateResourceMock = $this->createMock(Rate::class);
        $this->joinProcessorMock = $this->createMock(
            JoinProcessorInterface::class
        );
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
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
        $countryMock = $this->createMock(Country::class);
        $countryMock->expects($this->any())->method('getId')->willReturn(1);
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('create')->willReturn($countryMock);

        $regionId = 2;
        $regionMock = $this->createMock(Region::class);
        $regionMock->expects($this->any())->method('getId')->willReturn($regionId);
        $regionMock->expects($this->any())->method('load')->with($regionId)->willReturnSelf();
        $this->regionFactoryMock->expects($this->once())->method('create')->willReturn($regionMock);

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
            ->with($rateMock)->willReturn($rateTitles);
        $this->rateResourceMock->expects($this->once())->method('save')->with($rateMock);
        $rateMock->expects($this->once())->method('saveTitles')->with($rateTitles);
        $this->rateRegistryMock->expects($this->once())->method('registerTaxRate')->with($rateMock);

        $this->model->save($rateMock);
    }

    public function testSaveThrowsExceptionIfTargetTaxRateDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such entity with id 9999');
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
            ->willReturn($rateMock);
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->deleteById($rateId);
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $rateMock = $this->getTaxRateMock([]);

        $objectManager = new ObjectManager($this);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            Collection::class,
            $items
        );
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->willReturn(count($items));

        $this->rateFactoryMock->expects($this->once())->method('create')->willReturn($rateMock);
        $rateMock->expects($this->any())->method('getCollection')->willReturn($collectionMock);

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
     * @return MockObject
     */
    private function getTaxRateMock(array $taxRateData)
    {
        $taxRateMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate::class);
        foreach ($taxRateData as $key => $value) {
            // convert key from snake case to upper case
            $taxRateMock->expects($this->any())
                ->method('get' . str_replace('_', '', ucwords($key, '_')))
                ->willReturn($value);
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
     * @throws AlreadyExistsException
     */
    public function testSaveThrowsExceptionIfCannotSaveTitles($expectedException, $exceptionType, $exceptionMessage)
    {
        $countryCode = 'US';
        $countryMock = $this->createMock(Country::class);
        $countryMock->expects($this->any())->method('getId')->willReturn(1);
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('create')->willReturn($countryMock);

        $regionId = 2;
        $regionMock = $this->createMock(Region::class);
        $regionMock->expects($this->any())->method('getId')->willReturn($regionId);
        $regionMock->expects($this->any())->method('load')->with($regionId)->willReturnSelf();
        $this->regionFactoryMock->expects($this->once())->method('create')->willReturn($regionMock);

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
            ->with($rateMock)->willReturn($rateTitles);
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
    public static function saveThrowsExceptionIfCannotSaveTitlesDataProvider()
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
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $objectManager = new ObjectManager($this);
        $rateMock = $this->getTaxRateMock([]);
        $items = [$rateMock];
        $collectionMock = $objectManager->getCollectionMock(
            Collection::class,
            $items
        );
        $rateMock = $this->getTaxRateMock([]);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('getSize')->willReturn(count($items));

        $this->rateFactoryMock->expects($this->once())->method('create')->willReturn($rateMock);
        $rateMock->expects($this->any())->method('getCollection')->willReturn($collectionMock);

        $this->searchResultMock->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultMock);

        $this->joinProcessorMock->expects($this->once())->method('process')->with($collectionMock);

        $this->model->getList($searchCriteriaMock);
    }

    public function testValidate()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('One or more input exceptions have occurred.');
        $regionId = 2;
        $rateTitles = ['Label 1', 'Label 2'];
        $regionMock = $this->createMock(Region::class);
        $regionMock->expects($this->any())->method('getId')->willReturn('');
        $regionMock->expects($this->any())->method('load')->with($regionId)->willReturnSelf();
        $this->regionFactoryMock->expects($this->once())->method('create')->willReturn($regionMock);
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

    public function testValidateWithNoRate()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('"percentage_rate" is required. Enter and try again.');
        $rateTitles = ['Label 1', 'Label 2'];

        $countryCode = 'US';
        $countryMock = $this->createMock(Country::class);
        $countryMock->expects($this->any())->method('getId')->willReturn(1);
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('create')->willReturn($countryMock);

        $regionId = 2;
        $regionMock = $this->createMock(Region::class);
        $regionMock->expects($this->any())->method('getId')->willReturn($regionId);
        $regionMock->expects($this->any())->method('load')->with($regionId)->willReturnSelf();
        $this->regionFactoryMock->expects($this->once())->method('create')->willReturn($regionMock);

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

    public function testValidateWithWrongRate()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('"percentage_rate" is required. Enter and try again.');
        $rateTitles = ['Label 1', 'Label 2'];

        $countryCode = 'US';
        $countryMock = $this->createMock(Country::class);
        $countryMock->expects($this->any())->method('getId')->willReturn(1);
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('create')->willReturn($countryMock);

        $regionId = 2;
        $regionMock = $this->createMock(Region::class);
        $regionMock->expects($this->any())->method('getId')->willReturn($regionId);
        $regionMock->expects($this->any())->method('load')->with($regionId)->willReturnSelf();
        $this->regionFactoryMock->expects($this->once())->method('create')->willReturn($regionMock);

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
