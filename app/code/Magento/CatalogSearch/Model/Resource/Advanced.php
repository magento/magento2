<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

/**
 * Advanced Catalog Search resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Advanced extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $resourcePrefix = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize connection and define catalog product table as main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Prepare search condition for attribute
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param string|array $value
     * @return string|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareCondition($attribute, $value)
    {
        $condition = false;

        if (is_array($value)) {
            if ($attribute->getBackendType() == 'varchar') { // multiselect
                // multiselect
                $condition = ['in_set' => $value];
            } elseif (!isset($value['from']) && !isset($value['to'])) { // select
                // select
                $condition = ['in' => $value];
            } elseif (isset($value['from']) && '' !== $value['from'] || isset($value['to']) && '' !== $value['to']) {
                // range
                $condition = $value;
            }
        } else {
            if (strlen($value) > 0) {
                if (in_array($attribute->getBackendType(), ['varchar', 'text', 'static'])) {
                    $condition = ['like' => '%' . $value . '%']; // text search
                } else {
                    $condition = $value;
                }
            }
        }

        return $condition;
    }
}
