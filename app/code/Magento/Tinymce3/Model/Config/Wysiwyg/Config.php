<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Config\Wysiwyg;

class Config implements \Magento\Config\Model\Wysiwyg\ConfigInterface
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
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($config)
    {
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
