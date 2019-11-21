<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Widget\Model\Widget;

/**
 * Widgets Insertion Plugin Config for Editor HTML Element
 */
class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Widget\Model\WidgetFactory
     */
    protected $_widgetFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Widget\Model\WidgetFactory $widgetFactory
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Widget\Model\WidgetFactory $widgetFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Registry $registry
    ) {
        $this->_backendUrl = $backendUrl;
        $this->urlDecoder = $urlDecoder;
        $this->_assetRepo = $assetRepo;
        $this->_widgetFactory = $widgetFactory;
        $this->urlEncoder = $urlEncoder;
        $this->registry = $registry;
    }

    /**
     * Return config settings for widgets insertion plugin based on editor element config
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $settings = $this->getPluginSettings($config);
        return $config->addData($settings);
    }

    /**
     * Return config settings for widgets insertion plugin based on editor element config
     *
     * @param \Magento\Framework\DataObject $config
     * @return array
     */
    public function getPluginSettings($config)
    {
        $widgetWysiwyg = [
            [
                'name' => 'magentowidget',
                'src' => $this->getWysiwygJsPluginSrc(),
                'options' => [
                    'window_url' => $this->getWidgetWindowUrl($config),
                    'types' => $this->getAvailableWidgets($config),
                    'error_image_url' => $this->getErrorImageUrl(),
                    'placeholders' => $this->getWidgetPlaceholderImageUrls(),
                ],
            ]
        ];

        $configPlugins = (array) $config->getData('plugins');

        $widgetConfig['plugins'] = array_merge($configPlugins, $widgetWysiwyg);
        return $widgetConfig;
    }

    /**
     * Return list of available placeholders for widget
     *
     * @return array
     */
    public function getWidgetPlaceholderImageUrls()
    {
        return $this->_widgetFactory->create()->getPlaceholderImageUrls();
    }

    /**
     * Return url to error image
     *
     * @return string
     */
    public function getErrorImageUrl()
    {
        return $this->_assetRepo->getUrl('Magento_Widget::error.png');
    }

    /**
     * Return url to wysiwyg plugin
     *
     * @return string
     */
    public function getWysiwygJsPluginSrc()
    {
        return $this->_assetRepo->getUrl('mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js');
    }

    /**
     * Return Widgets Insertion Plugin Window URL
     *
     * @param \Magento\Framework\DataObject $config Editor element config
     * @return string
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

        if (!empty($skipped)) {
            $params['skip_widgets'] = $this->encodeWidgetsToQuery($skipped);
        }
        return $this->_backendUrl->getUrl('adminhtml/widget/index', $params);
    }

    /**
     * Encode list of widget types into query param
     *
     * @param string[]|string $widgets List of widgets
     * @return string Query param value
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
     */
    public function decodeWidgetsFromQuery($queryParam)
    {
        $param = $this->urlDecoder->decode($queryParam);
        return preg_split('/\s*\,\s*/', $param, 0, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get available widgets.
     *
     * @param \Magento\Framework\DataObject $config Editor element config
     * @return array
     */
    public function getAvailableWidgets($config)
    {
        $result = [];

        if (!$config->hasData('widget_types')) {
            $allWidgets = $this->_widgetFactory->create()->getWidgetsArray();
            $skipped = $this->_getSkippedWidgets();
            foreach ($allWidgets as $widget) {
                if (is_array($skipped) && in_array($widget['type'], $skipped)) {
                    continue;
                }
                $result[$widget['type']] = $widget['name']->getText();
            }
        }

        return $result;
    }

    /**
     * Return array of widgets disabled for selection
     *
     * @return string[]
     */
    protected function _getSkippedWidgets()
    {
        return $this->registry->registry('skip_widgets');
    }
}
