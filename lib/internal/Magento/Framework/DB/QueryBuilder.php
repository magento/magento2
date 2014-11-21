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
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     * @return void
     */
    public function setResource(\Magento\Framework\Model\Resource\Db\AbstractDb $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return \Magento\Framework\DB\QueryInterface
     * @throws \Magento\Framework\Exception
     */
    public function create()
    {
        $mapper = $this->criteria->getMapperInterfaceName();
        $mapperInstance = $this->mapperFactory->create($mapper);
        $select = $mapperInstance->map($this->criteria);
        $query = $this->queryFactory->create(
            'Magento\Framework\DB\Query',
            [
                'select' => $select,
                'criteria' => $this->criteria,
                'resource' => $this->resource
            ]
        );

        return $query;
    }
}
