<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\WysiwygModifierInterface;

/**
 * Class WysiwygConfigModifier
 * @TODO: this class is tutorial how to create modifiers
 * it is empty and should be removed before release
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 101.0.0
 */
class WysiwygConfigModifier implements ModifierInterface
{
    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
