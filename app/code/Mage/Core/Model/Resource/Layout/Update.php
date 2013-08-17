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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout update resource model
 */
class Mage_Core_Model_Resource_Layout_Update extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @var Magento_Cache_FrontendInterface
     */
    private $_cache;

    /**
     * @param Mage_Core_Model_Resource $resource
     * @param Magento_Cache_FrontendInterface $cache
     */
    public function __construct(
        Mage_Core_Model_Resource $resource,
        Magento_Cache_FrontendInterface $cache
    ) {
        parent::__construct($resource);
        $this->_cache = $cache;
    }

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('core_layout_update', 'layout_update_id');
    }

    /**
     * Retrieve layout updates by handle
     *
     * @param string $handle
     * @param Mage_Core_Model_Theme $theme
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function fetchUpdatesByHandle($handle, Mage_Core_Model_Theme $theme, Mage_Core_Model_Store $store)
    {
        $bind = array(
            'layout_update_handle' => $handle,
            'theme_id' => $theme->getId(),
            'store_id' => $store->getId(),
        );
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
     * @return Varien_Db_Select
     */
    protected function _getFetchUpdatesByHandleSelect($loadAllUpdates = false)
    {
        //TODO Why it also loads layout updates for store_id=0, isn't it Admin Store View?
        //If 0 means 'all stores' why it then refers by foreign key to Admin in `core_store` and not to something named
        // 'All Stores'?

        $select = $this->_getReadAdapter()->select()
            ->from(array('layout_update' => $this->getMainTable()), array('xml'))
            ->join(array('link' => $this->getTable('core_layout_link')),
                'link.layout_update_id=layout_update.layout_update_id', '')
            ->where('link.store_id IN (0, :store_id)')
            ->where('link.theme_id = :theme_id')
            ->where('layout_update.handle = :layout_update_handle')
            ->order('layout_update.sort_order ' . Varien_Db_Select::SQL_ASC);

        if (!$loadAllUpdates) {
            $select->where('link.is_temporary = 0');
        }

        return $select;
    }

    /**
     * Update a "layout update link" if relevant data is provided
     *
     * @param Mage_Core_Model_Layout_Update|Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Layout_Update
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $data = $object->getData();
        if (isset($data['store_id']) && isset($data['theme_id'])) {
            $this->_getWriteAdapter()->insertOnDuplicate($this->getTable('core_layout_link'), array(
                'store_id'         => $data['store_id'],
                'theme_id'         => $data['theme_id'],
                'layout_update_id' => $object->getId(),
                'is_temporary'     => (int)$object->getIsTemporary(),
            ));
        }
        $this->_cache->clean();
        return parent::_afterSave($object);
    }
}
