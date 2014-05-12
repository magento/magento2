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
namespace Magento\Catalog\Model\Resource\Product\Indexer;

/**
 * Catalog Product Eav Indexer Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Eav extends AbstractIndexer
{
    /**
     * EAV Indexers by type
     *
     * @var array
     */
    protected $_types;

    /**
     * Eav source factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory
     */
    protected $_eavSourceFactory;

    /**
     * Eav decimal factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory
     */
    protected $_eavDecimalFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory
     * @param \Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory $eavSourceFactory
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory,
        \Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory $eavSourceFactory
    ) {
        $this->_eavDecimalFactory = $eavDecimalFactory;
        $this->_eavSourceFactory = $eavSourceFactory;
        parent::__construct($resource, $eavConfig);
    }

    /**
     * Define main index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
    }

    /**
     * Retrieve array of EAV type indexers
     *
     * @return array
     */
    public function getIndexers()
    {
        if (is_null($this->_types)) {
            $this->_types = array(
                'source' => $this->_eavSourceFactory->create(),
                'decimal' => $this->_eavDecimalFactory->create()
            );
        }

        return $this->_types;
    }

    /**
     * Retrieve indexer instance by type
     *
     * @param string $type
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Eav\AbstractEav
     * @throws \Magento\Framework\Model\Exception
     */
    public function getIndexer($type)
    {
        $indexers = $this->getIndexers();
        if (!isset($indexers[$type])) {
            throw new \Magento\Framework\Model\Exception(__('We found an unknown EAV indexer type "%1".', $type));
        }
        return $indexers[$type];
    }

    /**
     * Process product save.
     * Method is responsible for index support
     * when product was saved and assigned categories was changed.
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
     */
    public function catalogProductSave(\Magento\Index\Model\Event $event)
    {
        $productId = $event->getEntityPk();
        $data = $event->getNewData();

        /**
         * Check if filterable attribute values were updated
         */
        if (!isset($data['reindex_eav'])) {
            return $this;
        }

        foreach ($this->getIndexers() as $indexer) {
            /** @var $indexer \Magento\Catalog\Model\Resource\Product\Indexer\Eav\AbstractEav */
            $indexer->reindexEntities($productId);
        }

        return $this;
    }

    /**
     * Process Product Delete
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
     */
    public function catalogProductDelete(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['reindex_eav_parent_ids'])) {
            return $this;
        }

        foreach ($this->getIndexers() as $indexer) {
            /** @var $indexer \Magento\Catalog\Model\Resource\Product\Indexer\Eav\AbstractEav */
            $indexer->reindexEntities($data['reindex_eav_parent_ids']);
        }

        return $this;
    }

    /**
     * Process Product Mass Update
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
     */
    public function catalogProductMassAction(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['reindex_eav_product_ids'])) {
            return $this;
        }

        foreach ($this->getIndexers() as $indexer) {
            /** @var $indexer \Magento\Catalog\Model\Resource\Product\Indexer\Eav\AbstractEav */
            $indexer->reindexEntities($data['reindex_eav_product_ids']);
        }

        return $this;
    }

    /**
     * Process Catalog Eav Attribute Save
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
     */
    public function catalogEavAttributeSave(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['reindex_attribute'])) {
            return $this;
        }

        $indexer = $this->getIndexer($data['attribute_index_type']);

        $indexer->reindexAttribute($event->getEntityPk(), !empty($data['is_indexable']));

        return $this;
    }

    /**
     * Rebuild all index data
     *
     * @return $this
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        foreach ($this->getIndexers() as $indexer) {
            /** @var $indexer \Magento\Catalog\Model\Resource\Product\Indexer\Eav\AbstractEav */
            $indexer->reindexAll();
        }

        return $this;
    }

    /**
     * Retrieve temporary source index table name
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_eav_idx');
        }
        return $this->getTable('catalog_product_index_eav_tmp');
    }
}
