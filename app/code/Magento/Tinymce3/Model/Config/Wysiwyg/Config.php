<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tinymce3\Model\Config\Wysiwyg;

/**
 * Class Config adds information about required css files for tinymce3 editor
 * @deprecated 100.3.0 use \Magento\Cms\Model\Wysiwyg\DefaultConfigProvider instead
 */
class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
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
