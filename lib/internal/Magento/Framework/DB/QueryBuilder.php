<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Class QueryBuilder
 */
class QueryBuilder
{
    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface
     */
    protected $criteria;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\MapperFactory
     */
    protected $mapperFactory;

    /**
     * @var \Magento\Framework\DB\QueryFactory
     */
    protected $queryFactory;

    /**
     * @param \Magento\Framework\DB\MapperFactory $mapperFactory
     * @param \Magento\Framework\DB\QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Framework\DB\MapperFactory $mapperFactory,
        \Magento\Framework\DB\QueryFactory $queryFactory
    ) {
        $this->mapperFactory = $mapperFactory;
        $this->queryFactory = $queryFactory;
    }

    /**
     * Set source Criteria
     *
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @return void
     */
    public function setCriteria(\Magento\Framework\Api\CriteriaInterface $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Set Resource
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @return void
     */
    public function setResource(\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return \Magento\Framework\DB\QueryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create()
    {
        $mapper = $this->criteria->getMapperInterfaceName();
        $mapperInstance = $this->mapperFactory->create($mapper);
        $select = $mapperInstance->map($this->criteria);
        $query = $this->queryFactory->create(
            \Magento\Framework\DB\Query::class,
            [
                'select' => $select,
                'criteria' => $this->criteria,
                'resource' => $this->resource
            ]
        );

        return $query;
    }
}
