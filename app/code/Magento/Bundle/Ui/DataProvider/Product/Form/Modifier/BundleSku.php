<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Customize SKU field
 * @since 2.1.0
 */
class BundleSku extends AbstractModifier
{
    const CODE_SKU_TYPE = 'sku_type';

    /**
     * @var ArrayManager
     * @since 2.1.0
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     * @since 2.1.0
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(static::CODE_SKU_TYPE, $meta, null, 'children') . static::META_CONFIG_PATH,
            $meta,
            [
                'valueMap' => [
                    'false' => '1',
                    'true' => '0'
                ],
                'validation' => [
                    'required-entry' => false
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
