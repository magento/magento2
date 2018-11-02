<?php
namespace Magento\Catalog\Model\Product\Attribute;

class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory */
    private $attributeFactory;
    /** @var  \Magento\Catalog\Model\Product\Attribute\Repository */
    private $attributeRepository;
    /** @var \Magento\Framework\Api\DataObjectHelper */
    private $dataObjectHelper;
    protected function setUp()
    {
        $this->attributeFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class
        );
        $this->attributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Product\Attribute\Repository::class
        );
        $this->dataObjectHelper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Api\DataObjectHelper::class
        );
    }
    public function testCustomSourceModel()
    {
        $attrCode = 'custom_source_model';
        try {
            $this->attributeRepository->deleteById($attrCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ignore) {
        };
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        $attribute = $this->attributeFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $attribute,
            [
                'attribute_code' => $attrCode,
                'source_model' => '\Vendor\Custom\Model\Source',
                'default_frontend_label' => 'Attribute Label',
                'frontend_input' => 'select',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            \Magento\Catalog\Api\Data\ProductAttributeInterface::class
        );
        $sourceModel = $attribute->getSourceModel();
        $attribute = $this->attributeRepository->save($attribute);
        $this->assertEquals($attribute->getSourceModel(), $sourceModel);
    }
    public function testCustomBackendModel()
    {
        $attrCode = 'custom_backend_model';
        try {
            $this->attributeRepository->deleteById($attrCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ignore) {
        };
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        $attribute = $this->attributeFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $attribute,
            [
                'attribute_code' => $attrCode,
                'frontend_input' => 'select',
                'backend_model' => '\Vendor\Custom\Model\Backend',
                'default_frontend_label' => 'Attribute Label',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            \Magento\Catalog\Api\Data\ProductAttributeInterface::class
        );
        $backendModel = $attribute->getBackendModel();
        $attribute = $this->attributeRepository->save($attribute);
        $this->assertEquals($attribute->getBackendModel(), $backendModel);
    }
    public function testCustomBackendType()
    {
        $attrCode = 'custom_backend_type';
        try {
            $this->attributeRepository->deleteById($attrCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ignore) {
        };
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        $attribute = $this->attributeFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $attribute,
            [
                'attribute_code' => $attrCode,
                'frontend_input' => 'select',
                'backend_type' => 'custom',
                'default_frontend_label' => 'select_attribute_label',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            \Magento\Catalog\Api\Data\ProductAttributeInterface::class
        );
        $backendType = $attribute->getBackendType();
        $attribute = $this->attributeRepository->save($attribute);
        $this->assertEquals($attribute->getBackendType(), $backendType);
    }
}