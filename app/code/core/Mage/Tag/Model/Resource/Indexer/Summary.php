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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tag Indexer Model
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Resource_Indexer_Summary extends Mage_Catalog_Model_Resource_Product_Indexer_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('tag_summary', 'tag_id');
    }

    /**
     * Process tag save
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function tagSave(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['tag_reindex_tag_id'])) {
            return $this;
        }
        return $this->aggregate($data['tag_reindex_tag_id']);
    }

    /**
     * Process tag relation save
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function tagRelationSave(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['tag_reindex_tag_id'])) {
            return $this;
        }
        return $this->aggregate($data['tag_reindex_tag_id']);
    }

    /**
     * Process product save.
     * Method is responsible for index support when product was saved.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['tag_reindex_required'])) {
            return $this;
        }

        $tagIds = Mage::getModel('Mage_Tag_Model_Tag_Relation')
            ->setProductId($event->getEntityPk())
            ->getRelatedTagIds();

        return $this->aggregate($tagIds);
    }

    /**
     * Process product delete.
     * Method is responsible for index support when product was deleted
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function catalogProductDelete(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['tag_reindex_tag_ids'])) {
            return $this;
        }
        return $this->aggregate($data['tag_reindex_tag_ids']);
    }

    /**
     * Process product massaction
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function catalogProductMassAction(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['tag_reindex_tag_ids'])) {
            return $this;
        }
        return $this->aggregate($data['tag_reindex_tag_ids']);
    }

    /**
     * Reindex all tags
     *
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function reindexAll()
    {
        return $this->aggregate();
    }

    /**
     * Aggregate tags by specified ids
     *
     * @param null|int|array $tagIds
     * @return Mage_Tag_Model_Resource_Indexer_Summary
     */
    public function aggregate($tagIds = null)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $this->beginTransaction();

        try {
            if (!empty($tagIds)) {
                $writeAdapter->delete(
                    $this->getTable('tag_summary'), array('tag_id IN(?)' => $tagIds)
                );
            } else {
                $writeAdapter->delete($this->getTable('tag_summary'));
            }

            $select = $writeAdapter->select()
                ->from(
                    array('tr' => $this->getTable('tag_relation')),
                    array(
                        'tr.tag_id',
                        'tr.store_id',
                        'customers'         => 'COUNT(DISTINCT tr.customer_id)',
                        'products'          => 'COUNT(DISTINCT tr.product_id)',
                        'popularity'        => 'COUNT(tr.customer_id) + MIN('
                            . $writeAdapter->getCheckSql(
                                'tp.base_popularity IS NOT NULL',
                                'tp.base_popularity',
                                '0'
                                )
                            . ')'
                    )
                )
                ->joinInner(
                    array('cs' => $this->getTable('core_store')),
                    'cs.store_id = tr.store_id',
                    array()
                )
                ->joinInner(
                    array('pw' => $this->getTable('catalog_product_website')),
                    'cs.website_id = pw.website_id AND tr.product_id = pw.product_id',
                    array()
                )
                ->joinInner(
                    array('e' => $this->getTable('catalog_product_entity')),
                    'tr.product_id = e.entity_id',
                    array()
                )
                ->joinLeft(
                    array('tp' => $this->getTable('tag_properties')),
                    'tp.tag_id = tr.tag_id AND tp.store_id = tr.store_id',
                    array()
                )
                ->group(array(
                    'tr.tag_id',
                    'tr.store_id'
                ));

            $statusCond = $writeAdapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond);

            $visibilityCond = $writeAdapter
                ->quoteInto('!=?', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
            $this->_addAttributeToSelect($select, 'visibility', 'e.entity_id', 'cs.store_id', $visibilityCond);

            if (!empty($tagIds)) {
                $select->where('tr.tag_id IN(?)', $tagIds);
            }

            Mage::dispatchEvent('prepare_catalog_product_index_select', array(
                'select'        => $select,
                'entity_field'  => new Zend_Db_Expr('e.entity_id'),
                'website_field' => new Zend_Db_Expr('cs.website_id'),
                'store_field'   => new Zend_Db_Expr('cs.store_id')
            ));

            $writeAdapter->query(
                $select->insertFromSelect($this->getTable('tag_summary'), array(
                    'tag_id',
                    'store_id',
                    'customers',
                    'products',
                    'popularity'
                ))
            );


            $selectedFields = array(
                'tag_id'            => 'tag_id',
                'store_id'          => new Zend_Db_Expr(0),
                'customers'         => 'COUNT(DISTINCT customer_id)',
                'products'          => 'COUNT(DISTINCT product_id)',
                'popularity'        => 'COUNT(customer_id)'
            );

            $agregateSelect = $writeAdapter->select();
            $agregateSelect->from($this->getTable('tag_relation'), $selectedFields)
                ->group('tag_id');

            if (!empty($tagIds)) {
                $agregateSelect->where('tag_id IN(?)', $tagIds);
            }

            $writeAdapter->query(
                $agregateSelect->insertFromSelect($this->getTable('tag_summary'), array_keys($selectedFields))
            );
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }
}
