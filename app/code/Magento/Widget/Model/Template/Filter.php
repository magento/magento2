<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Template;

/**
 * Template Filter Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Filter extends \Magento\Cms\Model\Template\Filter
{
    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget
     * @since 2.0.0
     */
    protected $_widgetResource;

    /**
     * @var \Magento\Widget\Model\Widget
     * @since 2.0.0
     */
    protected $_widget;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Pelago\Emogrifier $emogrifier
     * @param \Magento\Email\Model\Source\Variables $configVariables
     * @param \Magento\Widget\Model\ResourceModel\Widget $widgetResource
     * @param \Magento\Widget\Model\Widget $widget
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Variable\Model\VariableFactory $coreVariableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\UrlInterface $urlModel,
        \Pelago\Emogrifier $emogrifier,
        \Magento\Email\Model\Source\Variables $configVariables,
        \Magento\Widget\Model\ResourceModel\Widget $widgetResource,
        \Magento\Widget\Model\Widget $widget
    ) {
        $this->_widgetResource = $widgetResource;
        $this->_widget = $widget;
        parent::__construct(
            $string,
            $logger,
            $escaper,
            $assetRepo,
            $scopeConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory,
            $appState,
            $urlModel,
            $emogrifier,
            $configVariables
        );
    }

    /**
     * General method for generate widget
     *
     * @param string[] $construction
     * @return string
     * @since 2.0.0
     */
    public function generateWidget($construction)
    {
        $params = $this->getParameters($construction[2]);

        // Determine what name block should have in layout
        $name = null;
        if (isset($params['name'])) {
            $name = $params['name'];
        }

        // validate required parameter type or id
        if (!empty($params['type'])) {
            $type = $params['type'];
        } elseif (!empty($params['id'])) {
            $preConfigured = $this->_widgetResource->loadPreconfiguredWidget($params['id']);
            $type = $preConfigured['widget_type'];
            $params = $preConfigured['parameters'];
        } else {
            return '';
        }

        // we have no other way to avoid fatal errors for type like 'cms/widget__link', '_cms/widget_link' etc.
        $xml = $this->_widget->getWidgetByClassType($type);
        if ($xml === null) {
            return '';
        }

        // define widget block and check the type is instance of Widget Interface
        $widget = $this->_layout->createBlock($type, $name, ['data' => $params]);
        if (!$widget instanceof \Magento\Widget\Block\BlockInterface) {
            return '';
        }

        return $widget->toHtml();
    }

    /**
     * Generate widget
     *
     * @param string[] $construction
     * @return string
     * @since 2.0.0
     */
    public function widgetDirective($construction)
    {
        return $this->generateWidget($construction);
    }

    /**
     * Retrieve media file URL directive
     *
     * @param string[] $construction
     * @return string
     * @since 2.0.0
     */
    public function mediaDirective($construction)
    {
        $params = $this->getParameters($construction[2]);
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $params['url'];
    }
}
