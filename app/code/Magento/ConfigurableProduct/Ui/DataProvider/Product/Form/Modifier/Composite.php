<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Data provider for Configurable products
 */
class Composite extends AbstractModifier
{
    /**
     * @var array
     */
    private static $availableProductTypes = [
        ConfigurableType::TYPE_CODE,
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL
    ];

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
     * @param LocatorInterface $locator
     * @param ObjectManagerInterface $objectManager
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ObjectManagerInterface $objectManager,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->objectManager = $objectManager;
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
        if (in_array($productTypeId, self::$availableProductTypes)) {
            $productId = $model->getId();
            $data[$productId]['affect_configurable_product_attributes'] = '1';
            //$data[$model->getId()]['configurable-matrix'] = $this->getConfigurableMatrix();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (in_array($this->locator->getProduct()->getTypeId(), self::$availableProductTypes)) {
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