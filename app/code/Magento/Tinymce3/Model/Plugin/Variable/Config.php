<?php
/**le
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin\Variable;

class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(\Magento\Framework\View\Asset\Repository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param \Magento\Variable\Model\Variable\Config $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetWysiwygJsPluginSrc(
        \Magento\Variable\Model\Variable\Config $subject,
        $result
    ) {
        $editorPluginJs = 'Magento_Tinymce3::wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js';
        //magento2ce/app/code/Magento/Tinymce3/view/adminhtml/web/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js
        return $this->assetRepo->getUrl($editorPluginJs);
    }
}
