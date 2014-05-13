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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Dashboard\Searches;

/**
 * Adminhtml dashboard last search keywords block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Top extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\CatalogSearch\Model\Resource\Query\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory
     */
    protected $_queriesFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory $queriesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory $queriesFactory,
        array $data = array()
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
        if (!$this->_moduleManager->isEnabled('Magento_CatalogSearch')) {
            return parent::_prepareCollection();
        }
        $this->_collection = $this->_queriesFactory->create();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = $this->getRequest()->getParam('store');
        } else if ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')) {
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
            array(
                'header' => __('Search Term'),
                'sortable' => false,
                'index' => 'name',
                'renderer' => 'Magento\Backend\Block\Dashboard\Searches\Renderer\Searchquery'
            )
        );

        $this->addColumn(
            'num_results',
            array('header' => __('Results'), 'sortable' => false, 'index' => 'num_results', 'type' => 'number')
        );

        $this->addColumn(
            'popularity',
            array('header' => __('Uses'), 'sortable' => false, 'index' => 'popularity', 'type' => 'number')
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
        return $this->getUrl('catalog/search/edit', array('id' => $row->getId()));
    }
}
