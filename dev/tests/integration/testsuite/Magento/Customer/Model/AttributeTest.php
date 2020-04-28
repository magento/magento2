<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Customer\Model\Attribute.
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Attribute
     */
    private $model;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var int|string
     */
    private $customerEntityType;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Attribute::class);
        $this->attributeRepository = $this->objectManager->get(AttributeRepositoryInterface::class);
        $this->customerEntityType = $this->objectManager->get(Config::class)
            ->getEntityType('customer')
            ->getId();
    }

    /**
     * Test Create -> Read -> Update -> Delete attribute operations.
     *
     * @return void
     */
    public function testCRUD(): void
    {
        $this->model->setAttributeCode('test')
            ->setEntityTypeId($this->customerEntityType)
            ->setFrontendLabel('test')
            ->setIsUserDefined(1);
        $crud = new \Magento\TestFramework\Entity($this->model, ['frontend_label' => uniqid()]);
        $crud->testCrud();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_customer.php
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Do not change entity type.
     *
     * @return void
     */
    public function testAttributeSaveWithChangedEntityType(): void
    {
        $attribute = $this->attributeRepository->get($this->customerEntityType, 'user_attribute');
        $attribute->setEntityTypeId(5);
        $attribute->save();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_customer.php
     *
     * @return void
     */
    public function testAttributeSaveWithoutChangedEntityType(): void
    {
        $attribute = $this->attributeRepository->get($this->customerEntityType, 'user_attribute');
        $attribute->setSortOrder(1250);
        $attribute->save();
    }
}
