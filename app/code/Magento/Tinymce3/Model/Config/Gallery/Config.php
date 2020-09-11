<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tinymce3\Model\Config\Gallery;

use Magento\Ui\Component\Form\Element\DataType\Media\OpenDialogUrl;

/**
 * Class Config adds information about required configurations to display media gallery of tinymce3 editor
 *
 * @deprecated 100.3.0 use \Magento\Cms\Model\Wysiwyg\DefaultConfigProvider instead
 */
class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var OpednDialogUrl
     */
    private $openDialogUrl;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param OpenDialogUrl $openDialogUrl
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        OpenDialogUrl $openDialogUrl
    ) {
        $this->backendUrl = $backendUrl;
        $this->openDialogUrl = $openDialogUrl;
    }

    /**
     * Returns media gallery config
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $config->addData(
            [
                'add_images' => true,
                'files_browser_window_url' => $this->backendUrl->getUrl($this->openDialogUrl->get()),
            ]
        );

        return $config;
    }
}
