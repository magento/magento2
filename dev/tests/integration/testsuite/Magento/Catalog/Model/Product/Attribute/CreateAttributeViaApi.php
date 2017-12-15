<?php
namespace Magento\Catalog\Model\Product\Attribute;

class CreateAttributeViaApi extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture
     */
    private $attributeFactory;
    private $attributeRepository;
    private $dataObjectHelper;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->attributeFactory = $objectManager->create(
            \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class
        );
        $this->attributeRepository = $objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Repository::class
        );
        $this->dataObjectHelper = $objectManager->create(
            \Magento\Framework\Api\DataObjectHelper::class
        );


    }

    public function testCustomSourceModel()
    {
        $attribute_code='select_with_custom_source';

        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->attributeFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $attribute,
            [
                'attribute_code' => $attribute_code,
                'source_model' => '\Vendor\Custom\Model\Source',
                'default_frontend_label' => 'select_attribute_label',
                'frontend_input' => 'select',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            \Magento\Catalog\Api\Data\ProductAttributeInterface::class
        );
        $sourceModel = $attribute->getSourceModel();

        $attribute = $this->attributeRepository->save($attribute);

        $this->assertEquals($attribute->getSourceModel(), $sourceModel);

        $this->attributeRepository->deleteById($attribute_code);
    }
}

