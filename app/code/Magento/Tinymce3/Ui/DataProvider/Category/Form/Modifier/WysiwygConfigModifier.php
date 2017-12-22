<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Ui\DataProvider\Category\Form\Modifier;

use Magento\Tinymce3\Model\Config\Source\Wysiwyg\Editor;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class WysiwygConfigModifier implements ModifierInterface
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
    ) {
        $this->activeEditor = $activeEditor;
    }

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
        if ($this->activeEditor->getWysiwygAdapterPath() === Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            $tinymceThreeSettings = [
                'add_variables' => false,
                'add_widgets' => false,
                'add_directives' => true
            ];

            $meta['content']['children']['description']['arguments']['data']['config']['wysiwygConfigData']
                = $tinymceThreeSettings;
        }

        return $meta;
    }
}
