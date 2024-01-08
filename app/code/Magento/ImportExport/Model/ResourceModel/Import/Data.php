<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import;

/**
 * ImportExport import data resource model
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements \IteratorAggregate
{
    /**
     * @var \Iterator
     */
    protected $_iterator = null;

    /**
     * Helper to encode/decode json
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('importexport_importdata', 'id');
    }

    /**
     * Retrieve an external iterator
     *
     * @Deprecated
     * @see getIteratorForCustomQuery
     * @return \Iterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), ['data'])->order('id ASC');
        $stmt = $connection->query($select);

        $stmt->setFetchMode(\Zend_Db::FETCH_NUM);
        if ($stmt instanceof \IteratorAggregate) {
            $iterator = $stmt->getIterator();
        } else {
            // Statement doesn't support iterating, so fetch all records and create iterator ourself
            $rows = $stmt->fetchAll();
            $iterator = new \ArrayIterator($rows);
        }

        return $iterator;
    }

    /**
     * Retrieve an external iterator for Custom Query
     *
     * @param array $ids
     * @return \Iterator
     */
    #[\ReturnTypeWillChange]
    public function getIteratorForCustomQuery($ids)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['data'])->order('id ASC');
        if ($ids) {
            $select = $select->where('id IN (?)', $ids);
        }
        $stmt = $connection->query($select);

        $stmt->setFetchMode(\Zend_Db::FETCH_NUM);
        if ($stmt instanceof \IteratorAggregate) {
            $iterator = $stmt->getIterator();
        } else {
            // Statement doesn't support iterating, so fetch all records and create iterator ourself
            $rows = $stmt->fetchAll();
            $iterator = new \ArrayIterator($rows);
        }

        return $iterator;
    }

    /**
     * Clean all bunches from table.
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function cleanBunches()
    {
        return $this->getConnection()->delete($this->getMainTable());
    }

    /**
     * Clean all processed bunches from table.
     *
     * @return void
     */
    public function cleanProcessedBunches()
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            'is_processed = 1 OR TIMESTAMPADD(DAY, 1, updated_at) < CURRENT_TIMESTAMP() '
        );
    }

    /**
     * Set Flag for Imported Rows
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markProcessedBunches(array $ids): void
    {
        $where = $ids ? ['id IN (?)' => $ids] : '';
        $this->getConnection()->update(
            $this->getMainTable(),
            ['is_processed' => '1'],
            $where
        );
    }

    /**
     * Return behavior from import data table.
     *
     * @param array|null $ids
     * @return string
     */
    public function getBehavior($ids = null)
    {
        return $this->getUniqueColumnDataWithIds('behavior', $ids);
    }

    /**
     * Return entity type code from import data table.
     *
     * @param array $ids
     * @return string
     */
    public function getEntityTypeCode($ids = null)
    {
        return $this->getUniqueColumnDataWithIds('entity', $ids);
    }

    /**
     * Return request data from import data table
     *
     * @param string $code parameter name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUniqueColumnData($code)
    {
        $connection = $this->getConnection();
        $values = array_unique($connection->fetchCol($connection->select()->from($this->getMainTable(), [$code])));

        if (count($values) != 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error in data structure: %1 values are mixed', $code)
            );
        }
        return $values[0];
    }

    /**
     * Retrieve Unique Column Data for specific Ids
     *
     * @param string $code
     * @param array $ids
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUniqueColumnDataWithIds($code, $ids = null)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), [$code]);
        if ($ids) {
            $select = $select->where('id IN (?)', $ids);
        }
        $values = array_unique($connection->fetchCol($select));

        if (count($values) != 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error in data structure: %1 values are mixed', $code)
            );
        }
        return $values[0];
    }

    /**
     * Get next bunch of validated rows.
     *
     * @return array|null
     */
    public function getNextBunch()
    {
        if (null === $this->_iterator) {
            $this->_iterator = $this->getIterator();
            $this->_iterator->rewind();
        }
        $dataRow = null;
        if ($this->_iterator->valid()) {
            $encodedData = $this->_iterator->current();
            if (!empty($encodedData[0])) {
                $dataRow = $this->jsonHelper->jsonDecode($encodedData[0]);
                $this->_iterator->next();
            }
        }
        if (!$dataRow) {
            $this->_iterator = null;
        }
        return $dataRow;
    }

    /**
     * Get next unique bunch of validated rows.
     *
     * @param array|null $ids
     * @return array|null
     */
    public function getNextUniqueBunch($ids = null)
    {
        if (null === $this->_iterator) {
            $this->_iterator = $this->getIteratorForCustomQuery($ids);
            $this->_iterator->rewind();
        }
        $dataRow = null;
        if ($this->_iterator->valid()) {
            $encodedData = $this->_iterator->current();
            if (!empty($encodedData[0])) {
                $dataRow = $this->jsonHelper->jsonDecode($encodedData[0]);
                $this->_iterator->next();
            }
        }
        if (!$dataRow) {
            $this->_iterator = null;
        }
        return $dataRow;
    }

    /**
     * Save import rows bunch.
     *
     * @param string $entity
     * @param string $behavior
     * @param array $data
     * @return int
     */
    public function saveBunch($entity, $behavior, array $data)
    {
        $encodedData = $this->jsonHelper->jsonEncode($data);

        if (json_last_error()!==JSON_ERROR_NONE && empty($encodedData)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Error in CSV: ' . json_last_error_msg())
            );
        }

        $this->getConnection()->insert(
            $this->getMainTable(),
            ['behavior' => $behavior, 'entity' => $entity, 'data' => $encodedData, 'is_processed' => '0']
        );
        return $this->getConnection()->lastInsertId($this->getMainTable());
    }
}
