<?php
/**
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
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Template Filter Model
 *
 * @category    Magento
 * @package     Magento_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Model\Template;

class Filter extends \Magento\Cms\Model\Template\Filter
{
    /**
     * @var \Magento\Widget\Model\Resource\Widget
     */
    protected $_widgetResource;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_widget;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\View\LayoutFactory $layoutFactory
     * @param \Magento\Widget\Model\Resource\Widget $widgetResource
     * @param \Magento\Widget\Model\Widget $widget
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\VariableFactory $coreVariableFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\View\LayoutInterface $layout,
        \Magento\View\LayoutFactory $layoutFactory,
        \Magento\Widget\Model\Resource\Widget $widgetResource,
        \Magento\Widget\Model\Widget $widget
    ) {
        $this->_widgetResource = $widgetResource;
        $this->_widget = $widget;
        parent::__construct(
            $logger,
            $coreData,
            $viewUrl,
            $coreStoreConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory
        );
    }

    /**
     * Generate widget
     *
     * @param array $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);

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
        $widget = $this->_layout->createBlock($type, $name, array('data' => $params));
        if (!$widget instanceof \Magento\Widget\Block\BlockInterface) {
            return '';
        }

        return $widget->toHtml();
    }
}
