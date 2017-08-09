<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Dashboard;

/**
 *  Dashboard last search keywords block
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Top extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\CollectionFactory
     */
    protected $_queriesFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/grid.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queriesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queriesFactory,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_queriesFactory = $queriesFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('topSearchGrid');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $this->_collection = $this->_queriesFactory->create();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = $this->getRequest()->getParam('store');
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else {
            $storeIds = '';
        }

        $this->_collection->setPopularQueryFilter($storeIds);

        $this->setCollection($this->_collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'search_query',
            [
                'header' => __('Search Term'),
                'sortable' => false,
                'index' => 'query_text',
                'renderer' => \Magento\Backend\Block\Dashboard\Searches\Renderer\Searchquery::class,
                'header_css_class' => 'col-search-query',
                'column_css_class' => 'col-search-query'
            ]
        );

        $this->addColumn(
            'num_results',
            ['header' => __('Results'), 'sortable' => false, 'index' => 'num_results', 'type' => 'number']
        );

        $this->addColumn(
            'popularity',
            ['header' => __('Uses'), 'sortable' => false, 'index' => 'popularity', 'type' => 'number']
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('search/term/edit', ['id' => $row->getId()]);
    }
}
