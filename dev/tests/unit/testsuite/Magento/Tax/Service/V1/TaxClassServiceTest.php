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

/**
 * Test for \Magento\Tax\Service\V1\TaxClassService
 */
class TaxClassServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Resource\TaxClass\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassCollectionFactory;

    /**
     * @var \Magento\Tax\Service\V1\Data\SearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Tax\Model\ClassModelFactory
     */
    private $taxClassModelFactoryMock;

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

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->taxClassModelFactoryMock = $this->getMockBuilder('Magento\Tax\Model\ClassModelFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'delete', 'save', 'getClassType', '__wakeup'])
            ->getMock();

        $this->taxClassModelFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxClassBuilder');

        $this->taxClassService = $this->createService();
    }

    public function testCreateTaxClass()
    {
        $taxClassId = 1;;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClass::TYPE_PRODUCT)
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
            ->setClassType(TaxClass::TYPE_PRODUCT)
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

    public function testGetTaxClass()
    {
        $taxClassId = 1;

        $taxClassDataObjectMock = $this->getMockBuilder('Magento\Tax\Service\V1\Data\TaxClass')
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->converterMock->expects($this->once())
            ->method('createTaxClassData')
            ->with($this->taxClassModelMock)
            ->will($this->returnValue($taxClassDataObjectMock));

        $this->assertEquals($taxClassDataObjectMock, $this->taxClassService->getTaxClass($taxClassId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with class_id = -9999
     */
    public function testGetTaxClassWithNoSuchEntityException()
    {
        $taxClassId = -9999;

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->taxClassService->getTaxClass($taxClassId);
    }

    public function testUpdateTaxClassSuccess()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClass::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock
            ->expects($this->exactly(2))
            ->method('getClassType')
            ->will($this->returnValue(TaxClass::TYPE_PRODUCT));

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
            ->setClassType(TaxClass::TYPE_PRODUCT)
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
            ->setClassType(TaxClass::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassSample);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateTaxClassNotExistingEntity()
    {
        $taxClassId = 1;

        $taxClassSample = $this->taxClassBuilder
            ->setClassType(TaxClass::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

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
            ->setClassType(TaxClass::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getClassType')
            ->will($this->returnValue(TaxClass::TYPE_PRODUCT));

        $convertedTaxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->setMethods(['getClassType', '__wakeup'])
            ->getMock();

        $convertedTaxClassModelMock
            ->expects($this->once())
            ->method('getClassType')
            ->will($this->returnValue(TaxClass::TYPE_CUSTOMER));

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
            ->setClassType(TaxClass::TYPE_PRODUCT)
            ->setClassName('Wholesale product')
            ->create();

        $this->taxClassModelMock
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock
            ->expects($this->exactly(2))
            ->method('getClassType')
            ->will($this->returnValue(TaxClass::TYPE_PRODUCT));

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
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));

        $this->assertFalse($this->taxClassService->deleteTaxClass($taxClassId));
    }

    public function testDeleteModelDeleteSuccess()
    {
        $taxClassId = 1;

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($taxClassId));

        $this->taxClassModelMock->expects($this->once())
            ->method('delete');

        $this->assertTrue($this->taxClassService->deleteTaxClass($taxClassId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteNonExistentModel()
    {
        $taxClassId = 1;

        $this->taxClassModelMock->expects($this->once())
            ->method('load')
            ->with($taxClassId)
            ->will($this->returnValue($this->taxClassModelMock));

        $this->taxClassModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->taxClassService->deleteTaxClass($taxClassId);
    }

    public function testSearch()
    {
        $collectionSize = 3;
        $currentPage = 1;
        $pageSize = 10;
        $searchCriteria = $this->createSearchCriteria();
        $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        /** @var \PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Tax\Model\Resource\TaxClass\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'getItems', 'addOrder'])
            ->getMock();
        $this->taxClassCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->exactly(2))->method('addFieldToFilter');
        $collectionMock->expects($this->any())->method('getSize')->will($this->returnValue($collectionSize));
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('addOrder')->with('class_name', 'ASC');

        /** @var \PHPUnit_Framework_MockObject_MockObject $taxClassModelMock */
        $taxClassModelMock = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())->method('getItems')->will($this->returnValue([$taxClassModelMock]));
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
            ->getMockBuilder('Magento\Tax\Service\V1\Data\SearchResultsBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->converterMock = $this->getMockBuilder('Magento\Tax\Model\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $taxClassService = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\TaxClassService',
            [
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory,
                'taxClassModelFactory' => $this->taxClassModelFactoryMock,
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
        $searchCriteria = $searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(10)
            ->setSortOrders(['class_name' => 1])
            ->create();
        return $searchCriteria;
    }
}
