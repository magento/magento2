<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin\Wysiwyg;

use Magento\Tinymce3\Model\Config\Source\Wysiwyg\Editor;

class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
    ) {
        $this->assetRepo = $assetRepo;
        $this->activeEditor = $activeEditor;
    }

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Config $subject
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Cms\Model\Wysiwyg\Config $subject,
        \Magento\Framework\DataObject $config
    ) {
        if ($this->activeEditor->getWysiwygAdapterPath() === Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            $config->addData([
                'popup_css' => $this->assetRepo->getUrl(
                    'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
                ),
                'content_css' => $this->assetRepo->getUrl(
                    'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
                )
            ]);
        }

        return $config;
    }
}
