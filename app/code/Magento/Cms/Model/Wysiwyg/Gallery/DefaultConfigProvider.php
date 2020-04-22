<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Gallery;

use Magento\Ui\Component\Form\Element\DataType\Media\OpenDialogUrl;

/**
 * @inheritdoc
 */
class DefaultConfigProvider implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @var array
     */
    private $windowSize;

    /**
     * @var string|null
     */
    private $currentTreePath;

    /**
     * @var OpednDialogUrl
     */
    private $openDialogUrl;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper
     * @param OpenDialogUrl $openDialogUrl
     * @param array $windowSize
     * @param string|null $currentTreePath
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper,
        OpenDialogUrl $openDialogUrl,
        array $windowSize = [],
        $currentTreePath = null
    ) {
        $this->backendUrl = $backendUrl;
        $this->imagesHelper = $imagesHelper;
        $this->openDialogUrl = $openDialogUrl;
        $this->windowSize = $windowSize;
        $this->currentTreePath = $currentTreePath;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $pluginData = (array) $config->getData('plugins');
        $imageData = [
            [
                'name' => 'image',
            ]
        ];

        $fileBrowserUrlParams = [];

        if (is_string($this->currentTreePath)) {
            $fileBrowserUrlParams = [
                'current_tree_path' => $this->imagesHelper->idEncode($this->currentTreePath),
            ];
        }

        return $config->addData(
            [
                'add_images' => true,
                'files_browser_window_url' => $this->backendUrl->getUrl(
                    $this->openDialogUrl->get(),
                    $fileBrowserUrlParams
                ),
                'files_browser_window_width' => $this->windowSize['width'],
                'files_browser_window_height' => $this->windowSize['height'],
                'plugins' => array_merge($pluginData, $imageData)
            ]
        );
    }
}
