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
 * @since 2.0.0
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements \IteratorAggregate
{
    /**
     * @var \Iterator
     * @since 2.0.0
     */
    protected $_iterator = null;

    /**
     * Helper to encode/decode json
     *
     * @var \Magento\Framework\Json\Helper\Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string $connectionName
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('importexport_importdata', 'id');
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Iterator
     * @since 2.0.0
     */
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
     * Clean all bunches from table.
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    public function cleanBunches()
    {
        return $this->getConnection()->delete($this->getMainTable());
    }

    /**
     * Return behavior from import data table.
     *
     * @return string
     * @since 2.0.0
     */
    public function getBehavior()
    {
        return $this->getUniqueColumnData('behavior');
    }

    /**
     * Return entity type code from import data table.
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityTypeCode()
    {
        return $this->getUniqueColumnData('entity');
    }

    /**
     * Return request data from import data table
     *
     * @param string $code parameter name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
     * Get next bunch of validated rows.
     *
     * @return array|null
     * @since 2.0.0
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
            if (array_key_exists(0, $encodedData) && $encodedData[0]) {
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
     * @since 2.0.0
     */
    public function saveBunch($entity, $behavior, array $data)
    {
        return $this->getConnection()->insert(
            $this->getMainTable(),
            ['behavior' => $behavior, 'entity' => $entity, 'data' => $this->jsonHelper->jsonEncode($data)]
        );
    }
}
