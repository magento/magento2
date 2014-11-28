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
namespace Magento\Tax\Model\Calculation;

use Magento\TestFramework\Helper\ObjectManager;

class RateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RateRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateBuilderMock;

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
    private $searchResultBuilder;

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

    public function setUp()
    {
        $this->rateBuilderMock = $this->getMock(
            'Magento\Tax\Api\Data\TaxRateDataBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->rateConverterMock = $this->getMock(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            array(),
            array(),
            '',
            false
        );
        $this->rateRegistryMock = $this->getMock(
            'Magento\Tax\Model\Calculation\RateRegistry',
            array(),
            array(),
            '',
            false
        );
        $this->searchResultBuilder = $this->getMock(
            'Magento\Tax\Api\Data\TaxRuleSearchResultsDataBuilder',
            array('setItems', 'setSearchCriteria', 'setTotalCount', 'create'),
            array(),
            '',
            false
        );
        $this->rateFactoryMock = $this->getMock(
            'Magento\Tax\Model\Calculation\RateFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->countryFactoryMock = $this->getMock(
            'Magento\Directory\Model\CountryFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->regionFactoryMock = $this->getMock(
            'Magento\Directory\Model\RegionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->rateResourceMock = $this->getMock(
            'Magento\Tax\Model\Resource\Calculation\Rate',
            array(),
            array(),
            '',
            false
        );
        $this->model = new RateRepository(
            $this->rateBuilderMock,
            $this->rateConverterMock,
            $this->rateRegistryMock,
            $this->searchResultBuilder,
            $this->rateFactoryMock,
            $this->countryFactoryMock,
            $this->regionFactoryMock,
            $this->rateResourceMock
        );
    }

    public function testSave()
    {
        $countryCode = 'US';
        $countryMock = $this->getMock('Magento\Directory\Model\Country', array(), array(), '', false);
        $countryMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $countryMock->expects($this->any())->method('loadByCode')->with($countryCode)->will($this->returnSelf());
        $this->countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryMock));

        $regionId = 2;
        $regionMock = $this->getMock('Magento\Directory\Model\Region', array(), array(), '', false);
        $regionMock->expects($this->any())->method('getId')->will($this->returnValue($regionId));
        $regionMock->expects($this->any())->method('load')->with($regionId)->will($this->returnSelf());
        $this->regionFactoryMock->expects($this->once())->method('create')->will($this->returnValue($regionMock));

        $rateTitles = array(
            'Label 1',
            'Label 2',
        );
        $rateMock = $this->getTaxRateMock(array(
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
        ));
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
        $rateTitles = array(
            'Label 1',
            'Label 2',
        );
        $rateId = 9999;
        $rateMock = $this->getTaxRateMock(array(
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
        ));
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
        $rateMock = $this->getTaxRateMock(array('id' => 1));
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->delete($rateMock);
    }

    public function testDeleteById()
    {
        $rateId = 1;
        $rateMock = $this->getTaxRateMock(array('id' => $rateId));
        $this->rateRegistryMock->expects($this->once())->method('retrieveTaxRate')->with($rateId)
            ->will($this->returnValue($rateMock));
        $this->rateResourceMock->expects($this->once())->method('delete')->with($rateMock);
        $this->model->deleteById($rateId);
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteriaInterface');
        $searchCriteriaMock->expects($this->any())->method('getFilterGroups')->will($this->returnValue(array()));
        $searchCriteriaMock->expects($this->any())->method('getSortOrders')->will($this->returnValue(array()));
        $currentPage = 1;
        $pageSize = 100;
        $searchCriteriaMock->expects($this->any())->method('getCurrentPage')->will($this->returnValue($currentPage));
        $searchCriteriaMock->expects($this->any())->method('getPageSize')->will($this->returnValue($pageSize));
        $rateMock = $this->getTaxRateMock(array());

        $objectManager = new ObjectManager($this);
        $items = array($rateMock);
        $collectionMock = $objectManager->getCollectionMock(
            'Magento\Tax\Model\Resource\Calculation\Rate\Collection',
            $items
        );
        $collectionMock->expects($this->once())->method('joinRegionTable');
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('getSize')->will($this->returnValue(count($items)));

        $this->rateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateMock));
        $rateMock->expects($this->any())->method('getCollection')->will($this->returnValue($collectionMock));



        $this->searchResultBuilder->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->searchResultBuilder->expects($this->once())->method('setTotalCount')->with(count($items))
            ->willReturnSelf();
        $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultBuilder->expects($this->once())->method('create');

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
        $taxRateMock = $this->getMock('Magento\Tax\Model\Calculation\Rate', [], [], '', false);
        foreach ($taxRateData as $key => $value) {
            // convert key from snake case to upper case
            $taxRateMock->expects($this->any())
                ->method('get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                ->will($this->returnValue($value));
        }

        return $taxRateMock;
    }
}
