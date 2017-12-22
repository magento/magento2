<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Category\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class WysiwygConfigModifier implements ModifierInterface
{
    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $settings = [
            'height' => '100px',
            'add_variables' => false,
            'add_widgets' => false,
            'add_images' => true,
            'add_directives' => true
        ];

        $meta['content']['children']['description']['arguments']['data']['config']['wysiwygConfigData']
            = $settings;

        return $meta;
    }
}
