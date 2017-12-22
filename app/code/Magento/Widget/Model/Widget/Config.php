<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

/**
 * Widgets Insertion Plugin Config for Editor HTML Element
 */
class Config implements \Magento\Config\Model\Wysiwyg\ConfigInterface
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
     * List of postProcessors by adapter type
     *
     * @var array
     */
    private $postProcessors;

    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

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
        \Magento\Framework\Registry $registry,
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor,
        array $postProcessors = []
    ) {
        $this->_backendUrl = $backendUrl;
        $this->urlDecoder = $urlDecoder;
        $this->_assetRepo = $assetRepo;
        $this->_widgetFactory = $widgetFactory;
        $this->urlEncoder = $urlEncoder;
        $this->registry = $registry;
        $this->postProcessors = $postProcessors;
        $this->activeEditor = $activeEditor;
    }

    /**
     * Return config settings for widgets insertion plugin based on editor element config
     *
     * @param \Magento\Framework\DataObject $config
     * @return array
     */
    public function getConfig($config)
    {
        $widgetWysiwyg = [
            [
                'name' => 'magentowidget',
                'src' => $this->getWysiwygJsPluginSrc(),
                'options' => [
                    'window_url' => $this->getWidgetWindowUrl($config),
                    'types' => $this->getAvailableWidgets($config),
                    'error_image_url' => $this->getErrorImageUrl()
                ],
            ]
        ];

        $configPlugins = $config->getData('plugins');

        $widgetConfig = [
            'plugins' => array_merge($configPlugins, $widgetWysiwyg),
            'widget_placeholders' => $this->_widgetFactory->create()->getPlaceholderImageUrls(),
        ];
        return $widgetConfig;
    }

    /**
     * Return url to error image
     * @return string
     */
    public function getErrorImageUrl()
    {
        return $this->_assetRepo->getUrl('Magento_Widget::error.png');
    }

    /**
     * Return url to wysiwyg plugin
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
     * @param \Magento\Framework\DataObject $config Editor element config
     * @return array
     */
    private function getAvailableWidgets($config)
    {
        if (!$config->hasData('widget_types')) {
            $result = [];
            $allWidgets = $this->_widgetFactory->create()->getWidgetsArray();
            $skipped = $this->_getSkippedWidgets();
            foreach ($allWidgets as $widget) {
                if (is_array($skipped) && in_array($widget['type'], $skipped)) {
                    continue;
                }
                $result[] = $widget['name']->getText();
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
