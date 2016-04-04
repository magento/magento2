<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Customer;

/**
 * Customers collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * Name of collection model
     */
    const CUSTOMER_MODEL_NAME = 'Magento\Customer\Model\Customer';

    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    protected $_fieldsetConfig;

    /**
     * @var string
     */
    protected $_modelName;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $modelName
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_modelName = $modelName;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $connection
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->_modelName, 'Magento\Customer\Model\ResourceModel\Customer');
    }

    /**
     * Group result by customer email
     *
     * @return $this
     */
    public function groupByEmail()
    {
        $this->getSelect()->from(
            ['email' => $this->getEntity()->getEntityTable()],
            ['email_count' => new \Zend_Db_Expr('COUNT(email.entity_id)')]
        )->where(
            'email.entity_id = e.entity_id'
        )->group(
            'email.email'
        );

        return $this;
    }

    /**
     * Add Name to select
     *
     * @return $this
     */
    public function addNameToSelect()
    {
        $fields = [];
        $customerAccount = $this->_fieldsetConfig->getFieldset('customer_account');
        foreach ($customerAccount as $code => $field) {
            if (isset($field['name'])) {
                $fields[$code] = $code;
            }
        }

        $connection = $this->getConnection();
        $concatenate = [];
        if (isset($fields['prefix'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                $connection->getConcatSql(['LTRIM(RTRIM({{prefix}}))', '\' \'']),
                '\'\''
            );
        }
        $concatenate[] = 'LTRIM(RTRIM({{firstname}}))';
        $concatenate[] = '\' \'';
        if (isset($fields['middlename'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                $connection->getConcatSql(['LTRIM(RTRIM({{middlename}}))', '\' \'']),
                '\'\''
            );
        }
        $concatenate[] = 'LTRIM(RTRIM({{lastname}}))';
        if (isset($fields['suffix'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{suffix}} IS NOT NULL AND {{suffix}} != \'\'',
                $connection->getConcatSql(['\' \'', 'LTRIM(RTRIM({{suffix}}))']),
                '\'\''
            );
        }

        $nameExpr = $connection->getConcatSql($concatenate);

        $this->addExpressionAttributeToSelect('name', $nameExpr, $fields);

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->resetJoinLeft();

        return $select;
    }

    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }
}
