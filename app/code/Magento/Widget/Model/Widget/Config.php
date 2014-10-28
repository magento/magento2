<?php
/**
 * Widgets Insertion Plugin Config for Editor HTML Element
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Widget\Model\Widget;

class Config
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
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Widget\Model\WidgetFactory
     */
    protected $_widgetFactory;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Widget\Model\WidgetFactory $widgetFactory
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Widget\Model\WidgetFactory $widgetFactory
    ) {
        $this->_backendUrl = $backendUrl;
        $this->_coreHelper = $coreHelper;
        $this->_assetRepo = $assetRepo;
        $this->_widgetFactory = $widgetFactory;
    }

    /**
     * Return config settings for widgets insertion plugin based on editor element config
     *
     * @param \Magento\Framework\Object $config
     * @return array
     */
    public function getPluginSettings($config)
    {
        $url = $this->_assetRepo->getUrl(
            'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js'
        );
        $settings = array(
            'widget_plugin_src' => $url,
            'widget_placeholders' => $this->_widgetFactory->create()->getPlaceholderImageUrls(),
            'widget_window_url' => $this->getWidgetWindowUrl($config)
        );

        return $settings;
    }

    /**
     * Return Widgets Insertion Plugin Window URL
     *
     * @param \Magento\Framework\Object $config Editor element config
     * @return string
     */
    public function getWidgetWindowUrl($config)
    {
        $params = array();

        $skipped = is_array($config->getData('skip_widgets')) ? $config->getData('skip_widgets') : array();
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
        $widgets = is_array($widgets) ? $widgets : array($widgets);
        $param = implode(',', $widgets);
        return $this->_coreHelper->urlEncode($param);
    }

    /**
     * Decode URL query param and return list of widgets
     *
     * @param string $queryParam Query param value to decode
     * @return string[] Array of widget types
     */
    public function decodeWidgetsFromQuery($queryParam)
    {
        $param = $this->_coreHelper->urlDecode($queryParam);
        return preg_split('/\s*\,\s*/', $param, 0, PREG_SPLIT_NO_EMPTY);
    }
}
