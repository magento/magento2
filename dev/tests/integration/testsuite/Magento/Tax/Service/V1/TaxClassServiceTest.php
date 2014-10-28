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
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Service\V1\Data\TaxClassBuilder;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\TestFramework\Helper\Bootstrap;

class TaxClassServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxClassService
     */
    private $taxClassService;

    /**
     * @var TaxClassBuilder
     */
    private $taxClassBuilder;

    /**
     * @var TaxClassModel
     */
    private $taxClassModel;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * @var array
     */
    private $predefinedTaxClasses;

    const SAMPLE_TAX_CLASS_NAME = 'Wholesale Customer';

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxClassService = $this->objectManager->create('Magento\Tax\Service\V1\TaxClassService');
        $this->taxClassBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxClassBuilder');
        $this->taxClassModel = $this->objectManager->create('Magento\Tax\Model\ClassModel');
        $this->predefinedTaxClasses = [
            TaxClassServiceInterface::TYPE_PRODUCT => 'Taxable Goods',
            TaxClassServiceInterface::TYPE_CUSTOMER => 'Retail Customer'
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage class_id is not expected for this request.
     */
    public function testCreateTaxClass()
    {
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassName(self::SAMPLE_TAX_CLASS_NAME)
            ->setClassType(TaxClassServiceInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);
        $this->assertEquals(self::SAMPLE_TAX_CLASS_NAME, $this->taxClassModel->load($taxClassId)->getClassName());

        //Create another one with created id. Make sure its not updating the existing Tax class
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassId($taxClassId)
            ->setClassName(self::SAMPLE_TAX_CLASS_NAME . uniqid())
            ->setClassType(TaxClassServiceInterface::TYPE_CUSTOMER)
            ->create();
        //Should not be allowed to set the classId. Will throw InputException
        $this->taxClassService->createTaxClass($taxClassDataObject);
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage A class with the same name already exists for ClassType PRODUCT.
     */
    public function testCreateTaxClassUnique()
    {
        //ClassType and name combination has to be unique.
        //Testing against existing Tax classes which are already setup when the instance is installed
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassName($this->predefinedTaxClasses[TaxClassModel::TAX_CLASS_TYPE_PRODUCT])
            ->setClassType(TaxClassServiceInterface::TYPE_PRODUCT)
            ->create();
        $this->taxClassService->createTaxClass($taxClassDataObject);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateTaxClassInvalidData()
    {
        $taxClassDataObject = $this->taxClassBuilder->setClassName(null)
            ->setClassType('')
            ->create();
        try {
            $this->taxClassService->createTaxClass($taxClassDataObject);
        } catch (InputException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('class_name is a required field.', $errors[0]->getMessage());
            $this->assertEquals('class_type is a required field.', $errors[1]->getMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxClass()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassName($taxClassName)
            ->setClassType(TaxClassServiceInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);
        $data = $this->taxClassService->getTaxClass($taxClassId);
        $this->assertEquals($taxClassId, $data->getClassId());
        $this->assertEquals($taxClassName, $data->getClassName());
        $this->assertEquals(TaxClassServiceInterface::TYPE_CUSTOMER, $data->getClassType());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with class_id = -9999
     */
    public function testGetTaxClassWithNoSuchEntityException()
    {
        $this->taxClassService->getTaxClass(-9999);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDeleteTaxClass()
    {
        $taxClassName = 'Delete Me';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);

        $this->assertTrue($this->taxClassService->deleteTaxClass($taxClassId));

        // Verify if the tax class is deleted
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            "No such entity with class_id = $taxClassId"
        );
        $this->taxClassService->deleteTaxClass($taxClassId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with class_id = 99999
     */
    public function testDeleteTaxClassInvalidData()
    {
        $nonexistentTaxClassId = 99999;
        $this->taxClassService->deleteTaxClass($nonexistentTaxClassId);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateTaxClassSuccess()
    {
        $taxClassName = 'New Class Name';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);
        $this->assertEquals($taxClassName, $this->taxClassModel->load($taxClassId)->getClassName());

        $updatedTaxClassName = 'Updated Class Name';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($updatedTaxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();

        $this->assertTrue($this->taxClassService->updateTaxClass($taxClassId, $taxClassDataObject));

        $this->assertEquals($updatedTaxClassName, $this->taxClassModel->load($taxClassId)->getClassName());
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the taxClassId field.
     */
    public function testUpdateTaxClassWithoutClassId()
    {
        $taxClassName = 'New Class Name';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $this->taxClassService->updateTaxClass("", $taxClassDataObject);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with class_id = 99999
     */
    public function testUpdateTaxClassWithInvalidClassId()
    {
        $taxClassName = 'New Class Name';
        $nonexistentTaxClassId = 99999;
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $this->taxClassService->updateTaxClass($nonexistentTaxClassId, $taxClassDataObject);
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Updating classType is not allowed.
     */
    public function testUpdateTaxClassWithChangingClassType()
    {
        $taxClassName = 'New Class Name';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);
        $this->assertEquals($taxClassName, $this->taxClassModel->load($taxClassId)->getClassName());

        $updatedTaxClassName = 'Updated Class Name';
        $taxClassDataObject = $this->taxClassBuilder->setClassName($updatedTaxClassName)
            ->setClassType(TaxClassModel::TAX_CLASS_TYPE_PRODUCT)
            ->create();

        $this->taxClassService->updateTaxClass($taxClassId, $taxClassDataObject);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxClassId()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassName($taxClassName)
            ->setClassType(TaxClassServiceInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassService->createTaxClass($taxClassDataObject);
        /** @var \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder $taxClassKeyBuilder */
        $taxClassKeyBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxClassKeyBuilder');
        $taxClassKeyTypeId = $taxClassKeyBuilder->populateWithArray(
            [
                TaxClassKey::KEY_TYPE => TaxClassKey::TYPE_ID,
                TaxClassKey::KEY_VALUE => $taxClassId,
            ]
        )->create();
        $this->assertEquals(
            $taxClassId,
            $this->taxClassService->getTaxClassId($taxClassKeyTypeId, TaxClassServiceInterface::TYPE_CUSTOMER)
        );
        $taxClassKeyTypeName = $taxClassKeyBuilder->populateWithArray(
            [
                TaxClassKey::KEY_TYPE => TaxClassKey::TYPE_NAME,
                TaxClassKey::KEY_VALUE => $taxClassName,
            ]
        )->create();
        $this->assertEquals(
            $taxClassId,
            $this->taxClassService->getTaxClassId($taxClassKeyTypeId, TaxClassServiceInterface::TYPE_CUSTOMER)
        );
        $this->assertNull($this->taxClassService->getTaxClassId(null));
        $this->assertEquals(
            null,
            $this->taxClassService->getTaxClassId($taxClassKeyTypeName, TaxClassServiceInterface::TYPE_PRODUCT)
        );
    }
}
