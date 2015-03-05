<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Resource\Import;

/**
 * ImportExport import data resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\Model\Resource\Db\AbstractDb implements \IteratorAggregate
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
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
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
     * @return \Iterator
     */
    public function getIterator()
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from($this->getMainTable(), ['data'])->order('id ASC');
        $stmt = $adapter->query($select);

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
        return $this->_getWriteAdapter()->delete($this->getMainTable());
    }

    /**
     * Return behavior from import data table.
     *
     * @return string
     */
    public function getBehavior()
    {
        return $this->getUniqueColumnData('behavior');
    }

    /**
     * Return entity type code from import data table.
     *
     * @return string
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
     */
    public function getUniqueColumnData($code)
    {
        $adapter = $this->_getReadAdapter();
        $values = array_unique($adapter->fetchCol($adapter->select()->from($this->getMainTable(), [$code])));

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
        if ($this->_iterator->valid()) {
            $dataRow = $this->_iterator->current();
            $dataRow = $this->jsonHelper->jsonDecode($dataRow[0]);
            $this->_iterator->next();
        } else {
            $this->_iterator = null;
            $dataRow = null;
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
        return $this->_getWriteAdapter()->insert(
            $this->getMainTable(),
            ['behavior' => $behavior, 'entity' => $entity, 'data' => $this->jsonHelper->jsonEncode($data)]
        );
    }
}
