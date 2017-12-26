<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Ui\DataProvider\Category\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\WysiwygModifierInterface;

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
        $tinymceThreeSettings = [
            'add_variables' => false,
            'add_widgets' => false,
            'add_directives' => true
        ];

        $meta['content']['children']['description']['arguments']['data']['config']['wysiwygConfigData']
            = $tinymceThreeSettings;

        return $meta;
    }
}
