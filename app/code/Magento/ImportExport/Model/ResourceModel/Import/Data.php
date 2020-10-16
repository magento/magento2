<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;

/**
 * ImportExport import data resource model
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse) Necessary to get current logged in user without modifying methods
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements \IteratorAggregate
{
    /**
     * Offline import user ID
     */
    private const DEFAULT_USER_ID = 0;
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
     * @var Session
     */
    private $authSession;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string|null $connectionName
     * @param Session|null $authSession
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $connectionName = null,
        ?Session $authSession = null
    ) {
        parent::__construct($context, $connectionName);
        $this->jsonHelper = $jsonHelper;
        $this->authSession = $authSession ?? ObjectManager::getInstance()->get(Session::class);
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
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), ['data'])->order('id ASC');
        $select = $this->prepareSelect($select);
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
        return $this->getConnection()->delete($this->getMainTable(), $this->prepareDelete([]));
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
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), [$code]);
        $select = $this->prepareSelect($select);
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
     */
    public function saveBunch($entity, $behavior, array $data)
    {
        $encodedData = $this->jsonHelper->jsonEncode($data);

        if (json_last_error()!==JSON_ERROR_NONE && empty($encodedData)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Error in CSV: ' . json_last_error_msg())
            );
        }

        return $this->getConnection()->insert(
            $this->getMainTable(),
            $this->prepareInsert(['behavior' => $behavior, 'entity' => $entity, 'data' => $encodedData])
        );
    }

    /**
     * Prepare select for query
     *
     * @param Select $select
     * @return Select
     */
    private function prepareSelect(Select $select): Select
    {
        // user_id is NULL part is for backward compatibility
        $select->where('user_id=? OR user_id is NULL', $this->getCurrentUserId() ?? self::DEFAULT_USER_ID);

        return $select;
    }

    /**
     * Prepare data for insert
     *
     * @param array $data
     * @return array
     */
    private function prepareInsert(array $data): array
    {
        $data['user_id'] = $this->getCurrentUserId() ?? self::DEFAULT_USER_ID;

        return $data;
    }

    /**
     * Prepare delete constraints
     *
     * @param array $where
     * @return array
     */
    private function prepareDelete(array $where): array
    {
        // user_id is NULL part is for backward compatibility
        $where['user_id=? OR user_id is NULL'] = $this->getCurrentUserId() ?? self::DEFAULT_USER_ID;

        return $where;
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    private function getCurrentUserId(): ?int
    {
        return $this->authSession->isLoggedIn() ? $this->authSession->getUser()->getId() : null;
    }
}
