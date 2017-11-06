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
 */
class WysiwygConfigModifier implements ModifierInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
