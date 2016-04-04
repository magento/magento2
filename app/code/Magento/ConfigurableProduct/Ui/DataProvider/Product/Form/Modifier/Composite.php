<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts;
use Magento\Catalog\Ui\AllowedProductTypes;

/**
 * Data provider for Configurable products
 */
class Composite extends AbstractModifier
{
    /**
     * @var array
     */
    private $modifiers = [];

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AssociatedProducts
     */
    private $associatedProducts;

    /**
     * @var AllowedProductTypes
     */
    protected $allowedProductTypes;

    /**
     * @param LocatorInterface $locator
     * @param ObjectManagerInterface $objectManager
     * @param AssociatedProducts $associatedProducts
     * @param AllowedProductTypes $allowedProductTypes,
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ObjectManagerInterface $objectManager,
        AssociatedProducts $associatedProducts,
        AllowedProductTypes $allowedProductTypes,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->objectManager = $objectManager;
        $this->associatedProducts = $associatedProducts;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $model */
        $model = $this->locator->getProduct();
        $productTypeId = $model->getTypeId();
        if ($this->allowedProductTypes->isAllowedProductType($this->locator->getProduct())) {
            $productId = $model->getId();
            $data[$productId]['affect_configurable_product_attributes'] = '1';

            if ($productTypeId === ConfigurableType::TYPE_CODE) {
                $data[$productId]['configurable-matrix'] = $this->associatedProducts->getProductMatrix();
                $data[$productId]['attributes'] = $this->associatedProducts->getProductAttributesIds();
                $data[$productId]['attribute_codes'] = $this->associatedProducts->getProductAttributesCodes();
                $data[$productId]['product']['configurable_attributes_data'] =
                    $this->associatedProducts->getConfigurableAttributesData();
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->allowedProductTypes->isAllowedProductType($this->locator->getProduct())) {
            foreach ($this->modifiers as $modifierClass) {
                /** @var ModifierInterface $bundleModifier */
                $modifier = $this->objectManager->get($modifierClass);
                if (!$modifier instanceof ModifierInterface) {
                    throw new \InvalidArgumentException(__(
                        'Type %1 is not an instance of %2',
                        $modifierClass,
                        ModifierInterface::class
                    ));
                }
                $meta = $modifier->modifyMeta($meta);
            }
        }
        return $meta;
    }
}
