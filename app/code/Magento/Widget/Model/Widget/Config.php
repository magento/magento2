<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

/**
 * Widgets Insertion Plugin Config for Editor HTML Element
 * @since 2.0.0
 */
class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Widget\Model\Widget
     * @since 2.0.0
     */
    protected $_widget;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     * @since 2.0.0
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     * @since 2.0.0
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Widget\Model\WidgetFactory
     * @since 2.0.0
     */
    protected $_widgetFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Widget\Model\WidgetFactory $widgetFactory
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Widget\Model\WidgetFactory $widgetFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder
    ) {
        $this->_backendUrl = $backendUrl;
        $this->urlDecoder = $urlDecoder;
        $this->_assetRepo = $assetRepo;
        $this->_widgetFactory = $widgetFactory;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * Return config settings for widgets insertion plugin based on editor element config
     *
     * @param \Magento\Framework\DataObject $config
     * @return array
     * @since 2.0.0
     */
    public function getPluginSettings($config)
    {
        $url = $this->_assetRepo->getUrl(
            'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js'
        );
        $settings = [
            'widget_plugin_src' => $url,
            'widget_placeholders' => $this->_widgetFactory->create()->getPlaceholderImageUrls(),
            'widget_window_url' => $this->getWidgetWindowUrl($config),
        ];

        return $settings;
    }

    /**
     * Return Widgets Insertion Plugin Window URL
     *
     * @param \Magento\Framework\DataObject $config Editor element config
     * @return string
     * @since 2.0.0
     */
    public function getWidgetWindowUrl($config)
    {
        $params = [];

        $skipped = is_array($config->getData('skip_widgets')) ? $config->getData('skip_widgets') : [];
        if ($config->hasData('widget_filters')) {
            $all = $this->_widgetFactory->create()->getWidgets();
            $filtered = $this->_widgetFactory->create()->getWidgets($config->getData('widget_filters'));
            foreach ($all as $code => $widget) {
                if (!isset($filtered[$code])) {
                    $skipped[] = $widget['@']['type'];
                }
            }
        }

        if (count($skipped) > 0) {
            $params['skip_widgets'] = $this->encodeWidgetsToQuery($skipped);
        }
        return $this->_backendUrl->getUrl('adminhtml/widget/index', $params);
    }

    /**
     * Encode list of widget types into query param
     *
     * @param string[]|string $widgets List of widgets
     * @return string Query param value
     * @since 2.0.0
     */
    public function encodeWidgetsToQuery($widgets)
    {
        $widgets = is_array($widgets) ? $widgets : [$widgets];
        $param = implode(',', $widgets);
        return $this->urlEncoder->encode($param);
    }

    /**
     * Decode URL query param and return list of widgets
     *
     * @param string $queryParam Query param value to decode
     * @return string[] Array of widget types
     * @since 2.0.0
     */
    public function decodeWidgetsFromQuery($queryParam)
    {
        $param = $this->urlDecoder->decode($queryParam);
        return preg_split('/\s*\,\s*/', $param, 0, PREG_SPLIT_NO_EMPTY);
    }
}
