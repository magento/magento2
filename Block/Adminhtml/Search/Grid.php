<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block\Adminhtml\Search;

/**
 * Search query relations edit grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\AdvancedSearch\Model\Adminhtml\Search\Grid\Options
     */
    protected $_options;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\AdvancedSearch\Model\Adminhtml\Search\Grid\Options $options
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\AdvancedSearch\Model\Adminhtml\Search\Grid\Options $options,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $backendHelper, $data);
        $this->_options = $options;
        $this->_registryManager = $registry;
        $this->setDefaultFilter(['query_id_selected' => 1]);
    }

    /**
     *  Retrieve a value from registry by a key
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->_registryManager->registry('current_catalog_search');
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for query selected flag
        if ($column->getId() == 'query_id_selected' && $this->getQuery()->getId()) {
            $selectedIds = $this->getSelectedQueries();
            if (empty($selectedIds)) {
                $selectedIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('query_id', ['in' => $selectedIds]);
            } elseif (!empty($selectedIds)) {
                $this->getCollection()->addFieldToFilter('query_id', ['nin' => $selectedIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected related queries from grid
     *
     * @return array
     */
    public function getSelectedQueries()
    {
        return $this->_options->toOptionArray();
    }

    /**
     * Get queries json
     *
     * @return string
     */
    public function getQueriesJson()
    {
        $queries = array_flip($this->getSelectedQueries());
        if (!empty($queries)) {
            return $this->jsonHelper->jsonEncode($queries);
        }
        return '{}';
    }
}
