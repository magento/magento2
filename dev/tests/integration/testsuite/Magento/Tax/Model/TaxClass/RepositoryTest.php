<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Exception\InputException;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\TestFramework\Helper\Bootstrap;

class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Repository
     */
    private $taxClassRepository;

    /**
     * @var TaxClassInterfaceFactory
     */
    private $taxClassFactory;

    /**
     * @var TaxClassModel
     */
    private $taxClassModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $predefinedTaxClasses;

    const SAMPLE_TAX_CLASS_NAME = 'Wholesale Customer';

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxClassRepository = $this->objectManager->create(\Magento\Tax\Api\TaxClassRepositoryInterface::class);
        $this->taxClassFactory = $this->objectManager->create(\Magento\Tax\Api\Data\TaxClassInterfaceFactory::class);
        $this->taxClassModel = $this->objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $this->predefinedTaxClasses = [
            TaxClassManagementInterface::TYPE_PRODUCT => 'Taxable Goods',
            TaxClassManagementInterface::TYPE_CUSTOMER => 'Retail Customer',
        ];
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSave()
    {
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName(self::SAMPLE_TAX_CLASS_NAME)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertEquals(self::SAMPLE_TAX_CLASS_NAME, $this->taxClassModel->load($taxClassId)->getClassName());
    }

    /**
     * @magentoDbIsolation enabled
     *
     */
    public function testSaveThrowsExceptionIfGivenTaxClassNameIsNotUnique()
    {
        $this->expectExceptionMessage("A class with the same name already exists for ClassType PRODUCT.");
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        //ClassType and name combination has to be unique.
        //Testing against existing Tax classes which are already setup when the instance is installed
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($this->predefinedTaxClasses[TaxClassModel::TAX_CLASS_TYPE_PRODUCT])
            ->setClassType(TaxClassManagementInterface::TYPE_PRODUCT);
        $this->taxClassRepository->save($taxClassDataObject);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveThrowsExceptionIfGivenDataIsInvalid()
    {
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName(null)
            ->setClassType('');
        try {
            $this->taxClassRepository->save($taxClassDataObject);
        } catch (InputException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('"class_name" is required. Enter and try again.', $errors[0]->getMessage());
            $this->assertEquals('"class_type" is required. Enter and try again.', $errors[1]->getMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGet()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $data = $this->taxClassRepository->get($taxClassId);
        $this->assertEquals($taxClassId, $data->getClassId());
        $this->assertEquals($taxClassName, $data->getClassName());
        $this->assertEquals(TaxClassManagementInterface::TYPE_CUSTOMER, $data->getClassType());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetList()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        /** @var \Magento\Tax\Api\Data\TaxClassSearchResultsInterface */
        $searchResult = $this->taxClassRepository->getList($searchCriteriaBuilder->create());
        $items = $searchResult->getItems();
        /** @var \Magento\Tax\Api\Data\TaxClassInterface */
        $taxClass = array_pop($items);
        $this->assertInstanceOf(\Magento\Tax\Api\Data\TaxClassInterface::class, $taxClass);
        $this->assertEquals($taxClassName, $taxClass->getClassName());
        $this->assertEquals($taxClassId, $taxClass->getClassId());
        $this->assertEquals(TaxClassManagementInterface::TYPE_CUSTOMER, $taxClass->getClassType());
    }

    public function testGetThrowsExceptionIfRequestedTaxClassDoesNotExist()
    {
        $this->expectExceptionMessage("No such entity with class_id = -9999");
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->taxClassRepository->get(-9999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteById()
    {
        $taxClassName = 'Delete Me';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);

        $this->assertTrue($this->taxClassRepository->deleteById($taxClassId));

        // Verify if the tax class is deleted
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage("No such entity with class_id = $taxClassId");
        $this->taxClassRepository->deleteById($taxClassId);
    }

    public function testDeleteByIdThrowsExceptionIfTargetTaxClassDoesNotExist()
    {
        $this->expectExceptionMessage("No such entity with class_id = 99999");
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $nonexistentTaxClassId = 99999;
        $this->taxClassRepository->deleteById($nonexistentTaxClassId);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveWithExistingTaxClass()
    {
        $taxClassName = 'New Class Name';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertEquals($taxClassName, $this->taxClassModel->load($taxClassId)->getClassName());

        $updatedTaxClassName = 'Updated Class Name';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($updatedTaxClassName)
            ->setClassId($taxClassId)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER);

        $this->assertEquals($taxClassId, $this->taxClassRepository->save($taxClassDataObject));

        $this->assertEquals($updatedTaxClassName, $this->taxClassModel->load($taxClassId)->getClassName());
    }

    /**
     * @magentoDbIsolation enabled
     *
     */
    public function testSaveThrowsExceptionIfTargetTaxClassHasDifferentClassType()
    {
        $this->expectExceptionMessage("Updating classType is not allowed.");
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $taxClassName = 'New Class Name';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertEquals($taxClassName, $this->taxClassModel->load($taxClassId)->getClassName());

        $updatedTaxClassName = 'Updated Class Name';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($updatedTaxClassName)
            ->setClassId($taxClassId)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_PRODUCT);

        $this->taxClassRepository->save($taxClassDataObject);
    }
}
