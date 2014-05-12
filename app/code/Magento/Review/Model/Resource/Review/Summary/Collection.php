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
namespace Magento\Review\Model\Resource\Review\Summary;

/**
 * Review summery collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Data\Collection\Db
{
    /**
     * Summary table name
     *
     * @var string
     */
    protected $_summaryTable;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\App\Resource $resource
    ) {
        $this->_setIdFieldName('primary_id');

        parent::__construct($entityFactory, $logger, $fetchStrategy, $resource->getConnection('review_read'));
        $this->_summaryTable = $resource->getTableName('review_entity_summary');

        $this->_select->from($this->_summaryTable);

        $this->setItemObjectClass('Magento\Review\Model\Review\Summary');
    }

    /**
     * Add entity filter
     *
     * @param int|string $entityId
     * @param int $entityType
     * @return $this
     */
    public function addEntityFilter($entityId, $entityType = 1)
    {
        $this->_select->where('entity_pk_value IN(?)', $entityId)->where('entity_type = ?', $entityType);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->_select->where('store_id = ?', $storeId);
        return $this;
    }
}
