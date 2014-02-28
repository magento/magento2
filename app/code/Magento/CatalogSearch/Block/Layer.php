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
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layered Navigation block for search
 *
 */
namespace Magento\CatalogSearch\Block;

use Magento\Catalog\Block\Layer\View;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\Layer as ModelLayer;
use Magento\CatalogSearch\Model\Resource\EngineProvider;
use Magento\Registry;
use Magento\View\Element\Template\Context;

class Layer extends View
{
    /**
     * Engine Provider
     *
     * @var EngineProvider
     */
    protected $_engineProvider;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog search data
     *
     * @var Data
     */
    protected $_catalogSearchData = null;

    /**
     * Catalog search layer
     *
     * @var ModelLayer
     */
    protected $_catalogSearchLayer;

    /**
     * @param Context $context
     * @param ModelLayer $catalogLayer
     * @param EngineProvider $engineProvider
     * @param Data $catalogSearchData
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModelLayer $catalogLayer,
        EngineProvider $engineProvider,
        Data $catalogSearchData,
        Registry $registry,
        array $data = array()
    ) {
        $this->_engineProvider = $engineProvider;
        $this->_coreRegistry = $registry;
        $this->_catalogSearchData = $catalogSearchData;
        parent::__construct($context, $catalogLayer, $data);
    }

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_coreRegistry->register('current_layer', $this->getLayer(), true);
    }

    /**
     * Initialize blocks names
     *
     * @return void
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        $this->_attributeFilterBlockName = 'Magento\CatalogSearch\Block\Layer\Filter\Attribute';
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        $_isLNAllowedByEngine = $this->_engineProvider->get()->isLayeredNavigationAllowed();
        if (!$_isLNAllowedByEngine) {
            return false;
        }
        $availableResCount = (int)$this->_storeManager->getStore()
            ->getConfig(ModelLayer::XML_PATH_DISPLAY_LAYER_COUNT);

        if (!$availableResCount
            || ($availableResCount > $this->getLayer()->getProductCollection()->getSize())
        ) {
            return parent::canShowBlock();
        }
        return false;
    }
}
