<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model\Wysiwyg\Gallery;

class DefaultConfigProvider implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var array
     */
    private $windowSize;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $windowSize
     */
    public function __construct(\Magento\Backend\Model\UrlInterface $backendUrl, array $windowSize = [])
    {
        $this->backendUrl = $backendUrl;
        $this->windowSize = $windowSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($config)
    {
        $pluginData = (array) $config->getData('plugins');
        $imageData = [
            [
                'name' => 'image',
            ]
        ];
        return $config->addData(
            [
                'add_images' => true,
                'files_browser_window_url' => $this->backendUrl->getUrl('cms/wysiwyg_images/index'),
                'files_browser_window_width' => $this->windowSize['width'],
                'files_browser_window_height' => $this->windowSize['height'],
                'plugins' => array_merge($pluginData, $imageData)
            ]
        );
    }
}
