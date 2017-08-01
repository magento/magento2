<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Helper\Product\Options;

use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;

/**
 * Class Factory
 * @api
 * @since 2.1.0
 */
class Factory
{
    /**
     * @var AttributeFactory
     * @since 2.1.0
     */
    private $attributeFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     * @since 2.1.0
     */
    private $productAttributeRepository;

    /**
     * @var Configurable
     * @since 2.1.0
     */
    private $productType;

    /**
     * @var OptionValueInterfaceFactory
     * @since 2.1.0
     */
    private $optionValueFactory;

    /**
     * Constructor
     *
     * @param Configurable $productType
     * @param AttributeFactory $attributeFactory
     * @param OptionValueInterfaceFactory $optionValueFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @since 2.1.0
     */
    public function __construct(
        Configurable $productType,
        AttributeFactory $attributeFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->productType = $productType;
        $this->attributeFactory = $attributeFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Create configurable product options
     *
     * @param array $attributesData
     * @return OptionInterface[]
     * @throws \InvalidArgumentException
     * @since 2.1.0
     */
    public function create(array $attributesData)
    {
        $options = [];

        foreach ($attributesData as $item) {
            $attribute = $this->attributeFactory->create();
            $eavAttribute = $this->productAttributeRepository->get($item[Attribute::KEY_ATTRIBUTE_ID]);

            if (!$this->productType->canUseAttribute($eavAttribute)) {
                throw new \InvalidArgumentException('Provided attribute can not be used with configurable product.');
            }

            $this->updateAttributeData($attribute, $item);
            $options[] = $attribute;
        }

        return $options;
    }

    /**
     * Update attribute data
     *
     * @param OptionInterface $attribute
     * @param array $item
     * @return void
     * @since 2.1.0
     */
    private function updateAttributeData(OptionInterface $attribute, array $item)
    {
        $values = [];
        foreach ($item['values'] as $value) {
            $option = $this->optionValueFactory->create();
            $option->setValueIndex($value['value_index']);
            $values[] = $option;
        }
        $attribute->setData(
            array_replace_recursive(
                (array)$attribute->getData(),
                $item
            )
        );
        $attribute->setValues($values);
    }
}
