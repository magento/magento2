<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Resource\Cms;

/**
 * Sitemap cms page collection model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Page extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Init resource model (catalog/category)
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_page', 'page_id');
    }

    /**
     * Retrieve cms page collection array
     *
     * @param int $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        $pages = [];

        $select = $this->_getWriteAdapter()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            'main_table.page_id = store_table.page_id',
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier != ?',
            \Magento\Cms\Model\Page::NOROUTE_PAGE_ID
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $query = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }

    /**
     * Prepare page object
     *
     * @param array $data
     * @return \Magento\Framework\Object
     */
    protected function _prepareObject(array $data)
    {
        $object = new \Magento\Framework\Object();
        $object->setId($data[$this->getIdFieldName()]);
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);

        return $object;
    }
}
