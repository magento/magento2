<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\Layout;

/**
 * Layout update resource model
 */
class Update extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cache;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Cache\FrontendInterface $cache)
    {
        parent::__construct($resource);
        $this->_cache = $cache;
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_layout_update', 'layout_update_id');
    }

    /**
     * Retrieve layout updates by handle
     *
     * @param string $handle
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function fetchUpdatesByHandle(
        $handle,
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Store\Model\Store $store
    ) {
        $bind = ['layout_update_handle' => $handle, 'theme_id' => $theme->getId(), 'store_id' => $store->getId()];
        $result = '';
        $readAdapter = $this->_getReadAdapter();
        if ($readAdapter) {
            $select = $this->_getFetchUpdatesByHandleSelect();
            $result = join('', $readAdapter->fetchCol($select, $bind));
        }
        return $result;
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

        $select = $this->_getReadAdapter()->select()->from(
            ['layout_update' => $this->getMainTable()],
            ['xml']
        )->join(
            ['link' => $this->getTable('core_layout_link')],
            'link.layout_update_id=layout_update.layout_update_id',
            ''
        )->where(
            'link.store_id IN (0, :store_id)'
        )->where(
            'link.theme_id = :theme_id'
        )->where(
            'layout_update.handle = :layout_update_handle'
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
     * @param \Magento\Core\Model\Layout\Update|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();
        if (isset($data['store_id']) && isset($data['theme_id'])) {
            $this->_getWriteAdapter()->insertOnDuplicate(
                $this->getTable('core_layout_link'),
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
