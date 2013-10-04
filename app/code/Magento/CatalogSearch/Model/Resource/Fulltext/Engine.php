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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CatalogSearch Fulltext Index Engine resource model
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Model\Resource\Fulltext;

class Engine extends \Magento\Core\Model\Resource\Db\AbstractDb
    implements \Magento\CatalogSearch\Model\Resource\EngineInterface
{
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Catalog search fulltext coll factory
     *
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext\CollectionFactory
     */
    protected $_catalogSearchFulltextCollFactory;

    /**
     * Catalog search advanced coll factory
     *
     * @var \Magento\CatalogSearch\Model\Resource\Advanced\CollectionFactory
     */
    protected $_catalogSearchAdvancedCollFactory;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Advanced
     */
    protected $_searchResource;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Advanced
     */
    protected $_searchResourceCollection;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_catalogSearchData = null;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\CollectionFactory $catalogSearchAdvancedCollFactory
     * @param \Magento\CatalogSearch\Model\Resource\Fulltext\CollectionFactory $catalogSearchFulltextCollFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\CatalogSearch\Model\Resource\Advanced $searchResource
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\Collection $searchResourceCollection
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,    
        \Magento\CatalogSearch\Model\Resource\Advanced\CollectionFactory $catalogSearchAdvancedCollFactory,
        \Magento\CatalogSearch\Model\Resource\Fulltext\CollectionFactory $catalogSearchFulltextCollFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogSearch\Model\Resource\Advanced $searchResource,
        \Magento\CatalogSearch\Model\Resource\Advanced\Collection $searchResourceCollection,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper
    ) {
        $this->_catalogSearchAdvancedCollFactory = $catalogSearchAdvancedCollFactory;
        $this->_catalogSearchFulltextCollFactory = $catalogSearchFulltextCollFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_searchResource = $searchResource;
        $this->_searchResourceCollection = $searchResourceCollection;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
    }

    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entity = 'product')
    {
        $this->_getWriteAdapter()->insert($this->getMainTable(), array(
            'product_id'    => $entityId,
            'store_id'      => $storeId,
            'data_index'    => $index
        ));
        return $this;
    }

    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product')
    {
        $data    = array();
        $storeId = (int)$storeId;
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = array(
                'product_id'    => (int)$entityId,
                'store_id'      => $storeId,
                'data_index'    => $index
            );
        }

        if ($data) {
            $this->_resourceHelper->insertOnDuplicate($this->getMainTable(), $data, array('data_index'));
        }

        return $this;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility()
    {
        return $this->_catalogProductVisibility->getVisibleInSearchIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * Remove entity data from fulltext search table
     *
     * @param int $storeId
     * @param int $entityId
     * @param string $entity 'product'|'cms'
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     */
    public function cleanIndex($storeId = null, $entityId = null, $entity = 'product')
    {
        $where = array();

        if (!is_null($storeId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('store_id=?', $storeId);
        }
        if (!is_null($entityId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('product_id IN (?)', $entityId);
        }

        // Delete locks reading queries and causes performance issues
        // Insert into index goes with ON_DUPLICATE options.
        // Insert into catalogsearch_result goes with catalog_product_entity inner join
        //$this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $this->_catalogSearchData->prepareIndexdata($index, $separator);
    }

    /**
     * Return resource model for the full text search
     *
     * @return \Magento\CatalogSearch\Model\Resource\Advanced
     */
    public function getResource()
    {
        return $this->_searchResource;
    }

    /**
     * Return resource collection model for the full text search
     *
     * @return \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    public function getResourceCollection()
    {
        return $this->_searchResourceCollection;
    }

    /**
     * Retrieve fulltext search result data collection
     *
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
     */
    public function getResultCollection()
    {
        return $this->_catalogSearchFulltextCollFactory->create();
    }

    /**
     * Retrieve advanced search result data collection
     *
     * @return \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->_catalogSearchAdvancedCollFactory->create();
    }

    /**
     * Define if Layered Navigation is allowed
     *
     * @return bool
     */
    public function isLayeredNavigationAllowed()
    {
        return true;
    }

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }
}
