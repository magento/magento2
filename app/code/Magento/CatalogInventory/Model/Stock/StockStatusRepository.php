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
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterfaceFactory;
use Magento\CatalogInventory\Model\Resource\Stock\Status as StockStatusResource;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class StockStatusRepository
 */
class StockStatusRepository implements StockStatusRepositoryInterface
{
    /**
     * @var StockStatusResource
     */
    protected $resource;

    /**
     * @var StatusFactory
     */
    protected $stockStatusFactory;

    /**
     * @var StockStatusCollectionInterfaceFactory
     */
    protected $stockStatusCollectionFactory;

    /**
     * @var QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * @var MapperFactory
     */
    protected $mapperFactory;

    /**
     * @param StockStatusResource $resource
     * @param StatusFactory $stockStatusFactory
     * @param StockStatusCollectionInterfaceFactory $collectionFactory
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param MapperFactory $mapperFactory
     */
    public function __construct(
        StockStatusResource $resource,
        StatusFactory $stockStatusFactory,
        StockStatusCollectionInterfaceFactory $collectionFactory,
        QueryBuilderFactory $queryBuilderFactory,
        MapperFactory $mapperFactory
    ) {
        $this->resource = $resource;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockStatusCollectionFactory = $collectionFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @param StockStatusInterface $stockStatus
     * @return StockStatusInterface
     * @throws CouldNotSaveException
     */
    public function save(StockStatusInterface $stockStatus)
    {
        try {
            $this->resource->save($stockStatus);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException($exception->getMessage());
        }
        return $stockStatus;
    }

    /**
     * @param string $stockStatusId
     * @return StockStatusInterface|Status
     */
    public function get($stockStatusId)
    {
        $stockStatus = $this->stockStatusFactory->create();
        $this->resource->load($stockStatus, $stockStatusId);
        return $stockStatus;
    }

    /**
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getList(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->stockStatusCollectionFactory->create(['query' => $query]);
        return $collection;
    }

    /**
     * @param StockStatusInterface $stockStatus
     * @return bool|true
     * @throws CouldNotDeleteException
     */
    public function delete(StockStatusInterface $stockStatus)
    {
        try {
            $this->resource->delete($stockStatus);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException($exception->getMessage());
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($id)
    {
        try {
            $stockStatus = $this->get($id);
            $this->delete($stockStatus);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException($exception->getMessage());
        }
        return true;
    }
}
