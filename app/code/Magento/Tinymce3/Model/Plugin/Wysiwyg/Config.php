<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin\Wysiwyg;

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
        $config->addData([
            'popup_css' => $this->assetRepo->getUrl(
                'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
            ),
            'content_css' => $this->assetRepo->getUrl(
                'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
            )
        ]);

        return $config;
    }
}
