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

use Magento\Framework\Exception\InputException;
use Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Service\V1\Data\TaxClassBuilder;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Test for \Magento\Tax\Service\V1\TaxClassService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxClassServiceTest extends \PHPUnit_Framework_TestCase
{
    const TAX_CLASS_ID = 'tax_class_id';
    const TAX_CLASS_NAME = 'tax_class_name';
    /**
     * @var \Magento\Tax\Model\Resource\TaxClass\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassCollectionFactory;

    /**
     * @var \Magento\Tax\Service\V1\Data\TaxClassSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultBuilder;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\ClassModelFactory
     */
    private $classModelRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\ClassModel
     */
    private $taxClassModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\Converter
     */
    private $converterMock;

    /**
     * @var TaxClassBuilder
     */
    private $taxClassBuilder;

    /**
     * @var TaxClassService
     */
    private $taxClassService;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Service\V1\Data\SortOrderBuilder;
     */
    private $sortOrderBuilder;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->classModelRegistryMock = $this->getMockBuilder('Magento\Tax\Model\ClassModelRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve', 'registerTaxClass'])
            ->getMock();

        $this->taxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'delete', 'save', 'getClassType', '__wakeup'])
            ->getMock();

        $this->classModelRegistryMock->expects($this->any())
            ->method('retrieve')
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxClassBuilder');

        $this->taxClassService = $this->createService();

        $this->sortOrderBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SortOrderBuilder'
        );
    }

    public function testCreateTaxClass()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('save');

        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassModel')
            ->with($taxClassSample)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->assertEquals($taxClassId, $this->taxClassService->createTaxClass($taxClassSample));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage A class with the same name already exists for ClassType PRODUCT.
     */
    public function testCreateTaxClassException()
    {
        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $exceptionMessage = \Magento\Tax\Model\Resource\TaxClass::UNIQUE_TAX_CLASS_MSG . ' already exists.';

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Magento\Framework\Model\Exception($exceptionMessage)));

        $this->taxClassModelMock
            ->expects($this->never())
            ->method('getId');

        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassModel')
            ->with($taxClassSample)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassService->createTaxClass($taxClassSample);
    }

    public function testCreateTaxClassInvalidData()
    {
        $taxClassSample = $this->taxClassBuilder
            ->create();

        $this->taxClassModelMock
            ->expects($this->never())
            ->method('save');

        $this->taxClassModelMock
            ->expects($this->never())
            ->method('getId');

        //Make sure that the conversion is avoided in case of data validation
        $this->converterMock
            ->expects($this->never())
            ->method('createTaxClassModel');

        try {
            $this->taxClassService->createTaxClass($taxClassSample);
        } catch (InputException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('class_name is a required field.', $errors[0]->getMessage());
            $this->assertEquals('class_type is a required field.', $errors[1]->getMessage());
        }
    }

    public function testUpdateTaxClassSuccess()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->exactly(2))
            ->method('getClassType')
            ->will($this->returnValue(TaxClassServiceInterface::TYPE_PRODUCT));

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('save');

        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassModel')
            ->with($taxClassSample)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->assertTrue($this->taxClassService->updateTaxClass($taxClassId, $taxClassSample));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage class_name is a required field.
     */
    public function testUpdateTaxClassInvalidDataNoClassName()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage class_type is a required field.
     */
    public function testUpdateTaxClassInvalidDataNoClassType()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "Invalid Class Type" provided for the class_type field.
     */
    public function testUpdateTaxClassInvalidDataInvalidClassType()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType('Invalid Class Type')
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage class_id is not expected for this request.
     */
    public function testUpdateTaxClassWithClassIdInDataObject()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassId($taxClassId)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the taxClassId field.
     */
    public function testUpdateTaxClassNoTaxClassId()
    {
        $taxClassId = null;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Updating classType is not allowed.
     */
    public function testUpdateTaxClassInvalidClassTypeSwitched()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getClassType')
            ->will($this->returnValue(TaxClassServiceInterface::TYPE_PRODUCT));

        $convertedTaxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->setMethods(['getClassType', '__wakeup'])
            ->getMock();

        $convertedTaxClassModelMock
            ->expects($this->once())
            ->method('getClassType')
            ->will($this->returnValue(TaxClassServiceInterface::TYPE_CUSTOMER));

        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassModel')
            ->with($taxClassSample)
            ->will($this->returnValue($convertedTaxClassModelMock));

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    public function testUpdateTaxClassSaveFailure()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->exactly(2))
            ->method('getClassType')
            ->will($this->returnValue(TaxClassServiceInterface::TYPE_PRODUCT));

        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassModel')
            ->with($taxClassSample)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));;

        $this->assertFalse($this->taxClassService->updateTaxClass($taxClassId, $taxClassSample));
    }

    public function testDeleteModelDeleteThrowsException()
    {
        $taxClassId = 1;

        $this->taxClassModelMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->assertFalse($this->taxClassService->deleteTaxClass($taxClassId));
    }

    public function testDeleteModelDeleteSuccess()
    {
        $taxClassId = 1;

        $this->taxClassModelMock->expects($this->once())
            ->method('delete');

        $this->assertTrue($this->taxClassService->deleteTaxClass($taxClassId));
    }

    public function testSearch()
    {
        $searchCriteria = $this->createSearchCriteria();
        $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);

        /** @var \PHPUnit_Framework_MockObject_MockObject $taxClassModelMock */
        $taxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->mockTaxClassCollection($taxClassModelMock);

        $this->taxClassCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        /** @var \PHPUnit_Framework_MockObject_MockObject $taxMock */
        $taxClassMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClass')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock
            ->expects($this->once())
            ->method('createTaxClassData')
            ->with($taxClassModelMock)
            ->will($this->returnValue($taxClassMock));
        $this->searchResultBuilder
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnValue([$taxClassMock]));

        $this->taxClassService->searchTaxClass($searchCriteria);
    }

    /**
     * @param string $expected
     * @param string[] $taxClassKeyMockValeMap
     * @param bool $found
     * @dataProvider getTaxClassIdDataProvider
     */
    public function testGetTaxClassId($expected, $taxClassKeyMockValeMap, $found = false)
    {
        /** @var \Magento\Tax\Service\V1\Data\TaxClassKey|\PHPUnit_Framework_MockObject_MockObject $taxClassKeyMock */
        $taxClassKeyMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClassKey')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockReturnValue($taxClassKeyMock, $taxClassKeyMockValeMap);

        if ($taxClassKeyMockValeMap['getType'] == TaxClassKey::TYPE_NAME) {
            $this->searchCriteriaBuilderMock->expects($this->exactly(2))
                ->method('addFilter')
                ->will($this->returnValue($this->searchCriteriaBuilderMock));
            /** @var \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria */
            $searchCriteria = $this->createSearchCriteria();
            $this->searchCriteriaBuilderMock->expects($this->once())
                ->method('create')
                ->will($this->returnValue($searchCriteria));
            $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);

            /** @var \PHPUnit_Framework_MockObject_MockObject $taxClassModelMock */
            $taxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
                ->disableOriginalConstructor()
                ->getMock();
            $collectionMock = $this->mockTaxClassCollection($taxClassModelMock);

            $this->taxClassCollectionFactory
                ->expects($this->once())
                ->method('create')
                ->will($this->returnValue($collectionMock));
            /** @var \PHPUnit_Framework_MockObject_MockObject $taxMock */
            $taxClassMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClass')
                ->disableOriginalConstructor()
                ->getMock();
            $this->converterMock
                ->expects($this->once())
                ->method('createTaxClassData')
                ->with($taxClassModelMock)
                ->will($this->returnValue($taxClassMock));
            $this->searchResultBuilder
                ->expects($this->once())
                ->method('setItems')
                ->will($this->returnValue([$taxClassMock]));
            $searchResultsMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClassSearchResults')
                ->disableOriginalConstructor()
                ->getMock();
            $searchResultsMock->expects($this->once())
                ->method('getItems')
                ->will($this->returnValue($found ? [$taxClassMock] : []));
            $taxClassMock->expects($this->any())
                ->method('getClassId')
                ->will($this->returnValue(self::TAX_CLASS_ID));
            $this->searchResultBuilder
                ->expects($this->once())
                ->method('create')
                ->will($this->returnValue($searchResultsMock));

        }
        $this->assertEquals($expected, $this->taxClassService->getTaxClassId($taxClassKeyMock));
    }

    public function testGetTaxClassIdEmptyTaxClassKey()
    {
        $this->assertNull($this->taxClassService->getTaxClassId(null));
    }

    public function getTaxClassIdDataProvider()
    {
        return [

            'type_id' => [
                self::TAX_CLASS_ID,
                [
                    'getType' => TaxClassKey::TYPE_ID,
                    'getValue' => self::TAX_CLASS_ID,
                ],
            ],
            'type_name_not_found' => [
                null,
                [
                    'getType' => TaxClassKey::TYPE_NAME,
                    'getValue' => self::TAX_CLASS_NAME,
                ],
            ],
            'type_name_found' => [
                self::TAX_CLASS_ID,
                [
                    'getType' => TaxClassKey::TYPE_NAME,
                    'getValue' => self::TAX_CLASS_NAME,
                ],
                true,
            ],
        ];
    }

    /**
     * @return TaxClassService
     */
    private function createService()
    {
        $this->taxClassCollectionFactory = $this
            ->getMockBuilder('Magento\Tax\Model\Resource\TaxClass\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getIterator'])
            ->getMock();

        $this->searchResultBuilder = $this
            ->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClassSearchResultsBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setSearchCriteria', 'setItems', 'setTotalCount', 'create'])
            ->getMock();

        $this->converterMock = $this->getMockBuilder('Magento\Tax\Model\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder'
        )->disableOriginalConstructor()
            ->getMock();

        $taxClassService = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\TaxClassService',
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory,
                'classModelRegistry' => $this->classModelRegistryMock,
                'searchResultsBuilder' => $this->searchResultBuilder,
                'converter' => $this->converterMock
            ]
        );

        return $taxClassService;
    }

    /**
     * @return \Magento\Framework\Service\V1\Data\SearchCriteria
     */
    private function createSearchCriteria()
    {
        /** @var \Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder $filterGroupBuilder */
        $filterGroupBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder'
        );
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        /** @var \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->getObject('Magento\Framework\Service\V1\Data\FilterBuilder');
        $productTaxClass = [TaxClass::KEY_NAME => 'Taxable Goods', TaxClass::KEY_TYPE => 'PRODUCT'];
        $customerTaxClass = [TaxClass::KEY_NAME => 'Retail Customer', TaxClass::KEY_TYPE => 'CUSTOMER'];

        $filter1 = $filterBuilder->setField(TaxClass::KEY_NAME)
            ->setValue($productTaxClass[TaxClass::KEY_NAME])
            ->create();
        $filter2 = $filterBuilder->setField(TaxClass::KEY_NAME)
            ->setValue($customerTaxClass[TaxClass::KEY_NAME])
            ->create();
        $filter3 = $filterBuilder->setField(TaxClass::KEY_TYPE)
            ->setValue($productTaxClass[TaxClass::KEY_TYPE])
            ->create();
        $filter4 = $filterBuilder->setField(TaxClass::KEY_TYPE)
            ->setValue($customerTaxClass[TaxClass::KEY_TYPE])
            ->create();

        /**
         * (class_name == 'Retail Customer' || class_name == 'Taxable Goods)
         * && ( class_type == 'CUSTOMER' || class_type == 'PRODUCT')
         */
        $searchCriteriaBuilder->addFilter([$filter1, $filter2]);
        $searchCriteriaBuilder->addFilter([$filter3, $filter4]);
        /**@var \Magento\Framework\Service\V1\Data\SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SortOrderBuilder'
        );
        /** @var \Magento\Framework\Service\V1\Data\SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField('class_name')->setDirection(SearchCriteria::SORT_ASC)->create();
        $searchCriteria = $searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(10)
            ->setSortOrders([$sortOrder])
            ->create();
        return $searchCriteria;
    }

    /**
     * @param $taxClassModelMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTaxClassCollection($taxClassModelMock)
    {
        $collectionSize = 3;
        $currentPage = 1;
        $pageSize = 10;
        /** @var \PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Tax\Model\Resource\TaxClass\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'getItems', 'addOrder'])
            ->getMock();
        $collectionMock->expects($this->exactly(2))->method('addFieldToFilter');
        $collectionMock->expects($this->any())->method('getSize')->will($this->returnValue($collectionSize));
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('addOrder')->with('class_name', 'ASC');
        $collectionMock->expects($this->once())->method('getItems')->will($this->returnValue([$taxClassModelMock]));
        return $collectionMock;
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
}
