<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Customize Price field
 * @since 2.1.0
 */
class BundlePrice extends AbstractModifier
{
    const CODE_PRICE_TYPE = 'price_type';
    const CODE_TAX_CLASS_ID = 'tax_class_id';

    /**
     * @var ArrayManager
     * @since 2.1.0
     */
    protected $arrayManager;

    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @since 2.1.0
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(static::CODE_PRICE_TYPE, $meta, null, 'children') . static::META_CONFIG_PATH,
            $meta,
            [
                'disabled' => (bool)$this->locator->getProduct()->getId(),
                'valueMap' => [
                    'false' => '1',
                    'true' => '0'
                ],
                'validation' => [
                    'required-entry' => false
                ]
            ]
        );

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                ProductAttributeInterface::CODE_PRICE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked'
                ]
            ]
        );

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                static::CODE_TAX_CLASS_ID,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'disabled' => 'ns = ${ $.ns }, index = ' . static::CODE_PRICE_TYPE . ':checked'
                ]
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
