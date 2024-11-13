<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Template;

use Magento\Email\Model\Template\Css;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Adapter\CssInliner;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\VariableResolverInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\VariableFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Model\Widget;
use Psr\Log\LoggerInterface;

/**
 * Template Filter Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Filter extends \Magento\Cms\Model\Template\Filter
{
    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget
     */
    protected $_widgetResource;

    /**
     * @var Widget
     */
    protected $_widget;

    /**
     * Filter constructor.
     * @param StringUtils $string
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @param ScopeConfigInterface $scopeConfig
     * @param VariableFactory $coreVariableFactory
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param LayoutFactory $layoutFactory
     * @param State $appState
     * @param UrlInterface $urlModel
     * @param Variables $configVariables
     * @param VariableResolverInterface $variableResolver
     * @param Css\Processor $cssProcessor
     * @param Filesystem $pubDirectory
     * @param CssInliner $cssInliner
     * @param \Magento\Widget\Model\ResourceModel\Widget $widgetResource
     * @param Widget $widget
     * @param array $variables
     * @param array $directiveProcessors
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StringUtils $string,
        LoggerInterface $logger,
        Escaper $escaper,
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        VariableFactory $coreVariableFactory,
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        LayoutFactory $layoutFactory,
        State $appState,
        UrlInterface $urlModel,
        Variables $configVariables,
        VariableResolverInterface $variableResolver,
        Css\Processor $cssProcessor,
        Filesystem $pubDirectory,
        CssInliner $cssInliner,
        \Magento\Widget\Model\ResourceModel\Widget $widgetResource,
        Widget $widget,
        $variables = [],
        array $directiveProcessors = []
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
            $configVariables,
            $variableResolver,
            $cssProcessor,
            $pubDirectory,
            $cssInliner,
            $variables,
            $directiveProcessors
        );
    }

    /**
     * General method for generate widget
     *
     * @param string[] $construction
     * @return string
     */
    public function generateWidget($construction)
    {
        $params = $this->getParameters($construction[2]);

        // Determine what name block should have in layout
        $name = null;
        if (isset($params['name'])) {
            $name = $params['name'];
        }

        if (isset($this->_storeId) && !isset($params['store_id'])) {
            $params['store_id'] = $this->_storeId;
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
        if (!$widget instanceof BlockInterface) {
            return '';
        }

        return $widget->toHtml();
    }

    /**
     * Generate widget
     *
     * @param string[] $construction
     * @return string
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
     */
    public function mediaDirective($construction)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $params = $this->getParameters(html_entity_decode($construction[2], ENT_QUOTES));
        return $this->_storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $params['url'];
    }
}
