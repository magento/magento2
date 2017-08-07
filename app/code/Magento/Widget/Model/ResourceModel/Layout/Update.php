<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Widget\Model\ResourceModel\Layout;

/**
 * Layout update resource model
 */
class Update extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cache;

    /**
     * @var array
     * @since 2.0.5
     */
    private $layoutUpdateCache;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Cache\FrontendInterface $cache,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_cache = $cache;
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('layout_update', 'layout_update_id');
    }

    /**
     * Retrieve layout updates by handle
     *
     * @param string $handle
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Framework\App\ScopeInterface $store
     * @return string
     */
    public function fetchUpdatesByHandle(
        $handle,
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Framework\App\ScopeInterface $store
    ) {
        $bind = ['theme_id' => $theme->getId(), 'store_id' => $store->getId()];
        $cacheKey = implode('-', $bind);
        if (!isset($this->layoutUpdateCache[$cacheKey])) {
            $this->layoutUpdateCache[$cacheKey] = [];
            foreach ($this->getConnection()->fetchAll($this->_getFetchUpdatesByHandleSelect(), $bind) as $layout) {
                if (!isset($this->layoutUpdateCache[$cacheKey][$layout['handle']])) {
                    $this->layoutUpdateCache[$cacheKey][$layout['handle']] = '';
                }
                $this->layoutUpdateCache[$cacheKey][$layout['handle']] .= $layout['xml'];
            }
        }
        return isset($this->layoutUpdateCache[$cacheKey][$handle]) ? $this->layoutUpdateCache[$cacheKey][$handle] : '';
    }

    /**
     * Get select to fetch updates by handle
     *
     * @param bool $loadAllUpdates
     * @return \Magento\Framework\DB\Select
     */
    protected function _getFetchUpdatesByHandleSelect($loadAllUpdates = false)
    {
        //@todo Why it also loads layout updates for store_id=0, isn't it Admin Store View?
        //If 0 means 'all stores' why it then refers by foreign key to Admin in `store` and not to something named
        // 'All Stores'?

        $select = $this->getConnection()->select()->from(
            ['layout_update' => $this->getMainTable()],
            ['xml', 'handle']
        )->join(
            ['link' => $this->getTable('layout_link')],
            'link.layout_update_id=layout_update.layout_update_id',
            ''
        )->where(
            'link.store_id IN (0, :store_id)'
        )->where(
            'link.theme_id = :theme_id'
        )->order(
            'layout_update.sort_order ' . \Magento\Framework\DB\Select::SQL_ASC
        );

        if (!$loadAllUpdates) {
            $select->where('link.is_temporary = 0');
        }

        return $select;
    }

    /**
     * Update a "layout update link" if relevant data is provided
     *
     * @param \Magento\Widget\Model\Layout\Update|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();
        if (isset($data['store_id']) && isset($data['theme_id'])) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable('layout_link'),
                [
                    'store_id' => $data['store_id'],
                    'theme_id' => $data['theme_id'],
                    'layout_update_id' => $object->getId(),
                    'is_temporary' => (int)$object->getIsTemporary()
                ]
            );
        }
        $this->_cache->clean();
        return parent::_afterSave($object);
    }
}
