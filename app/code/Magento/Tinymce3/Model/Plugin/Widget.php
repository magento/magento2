<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin;

use Magento\Tinymce3\Model\Config\Source\Wysiwyg\Editor;

/**
 * Plugin to override widget placeholder images in case if tinymce3 adapter is used
 */
class Widget
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * @var array
     */
    private $placeholderImages;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Tinymce3\Model\Config\Widget\PlaceholderImagesPool $placeholderImages
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Tinymce3\Model\Config\Widget\PlaceholderImagesPool $placeholderImages
    ) {
        $this->activeEditor = $activeEditor;
        $this->placeholderImages = $placeholderImages;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param $proceed
     * @param $type
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetPlaceholderImageUrl(
        \Magento\Widget\Model\Widget $subject,
        $proceed,
        $type
    ) {
        if ($this->activeEditor->getWysiwygAdapterPath() !== Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            return $proceed($type);
        }
        $placeholders = $this->placeholderImages->getWidgetPlaceholders();
        $defaultImage = $this->assetRepo->getUrl('Magento_Tinymce3::images/widget/placeholder.png');

        if (isset($placeholders[$type])) {
            return $this->assetRepo->getUrl($placeholders[$type]);
        }
        return $defaultImage;
    }
}
