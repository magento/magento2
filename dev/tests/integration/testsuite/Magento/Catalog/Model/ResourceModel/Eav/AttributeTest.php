<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Eav;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Catalog\Model\ResourceModel\Eav\Attribute.
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
    private $catalogProductEntityType;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Attribute::class);
        $this->attributeRepository = $this->objectManager->get(AttributeRepositoryInterface::class);
        $this->catalogProductEntityType = $this->objectManager->get(Config::class)
            ->getEntityType('catalog_product')
            ->getId();
    }

    /**
     * Test Create -> Read -> Update -> Delete attribute operations.
     *
     * @return void
     */
    public function testCRUD()
    {
        $this->model->setAttributeCode('test')
            ->setEntityTypeId($this->catalogProductEntityType)
            ->setFrontendLabel('test')
            ->setIsUserDefined(1);
        $crud = new \Magento\TestFramework\Entity($this->model, ['frontend_label' => uniqid()]);
        $crud->testCrud();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_attribute.php
     *
     * @return void
     */
    public function testAttributeSaveWithChangedEntityType(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage("Do not change entity type.");
        $attribute = $this->attributeRepository->get($this->catalogProductEntityType, 'test_attribute_code_333');
        $attribute->setEntityTypeId(1);
        $attribute->save();
    }
}
