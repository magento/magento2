<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;

/**
 * Class responsibly for persistence of prices.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PricePersistence
{
    /**
     * Price storage table.
     *
     * @var string
     */
    private $table = 'catalog_product_entity_decimal';

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * Metadata pool property to get a metadata.
     *
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Attribute code attribute to get the attribute id.
     *
     * @var string
     */
    private $attributeCode;

    /**
     * Attribute ID property to store the attribute id.
     *
     * @var int
     */
    private $attributeId;

    /**
     * Items per operation to chunk the array in a batch.
     *
     * @var int
     */
    private $itemsPerOperation = 500;

    /**
     * Product action property to update the attributes.
     *
     * @var Action
     */
    private $productAction;

    /**
     * Core Date to get the gm date.
     *
     * @var CoreDate
     */
    private $coreDate;

    /**
     * Date time property to format the date.
     *
     * @var DateTime
     */
    private $dateTime;

    /**
     * PricePersistence constructor.
     *
     * @param Attribute $attributeResource
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param ProductIdLocatorInterface $productIdLocator
     * @param MetadataPool $metadataPool
     * @param string $attributeCode
     * @param Action|null $productAction
     * @param CoreDate|null $coreDate
     * @param DateTime|null $dateTime
     */
    public function __construct(
        Attribute $attributeResource,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductIdLocatorInterface $productIdLocator,
        MetadataPool $metadataPool,
        $attributeCode = '',
        ?Action $productAction = null,
        ?CoreDate $coreDate = null,
        ?DateTime $dateTime = null
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->attributeCode = $attributeCode;
        $this->productIdLocator = $productIdLocator;
        $this->metadataPool = $metadataPool;
        $this->productAction = $productAction ?: ObjectManager::getInstance()
            ->get(Action::class);
        $this->coreDate = $coreDate ?: ObjectManager::getInstance()
            ->get(CoreDate::class);
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()
            ->get(DateTime::class);
    }

    /**
     * Get prices by SKUs.
     *
     * @param array $skus
     * @return array
     */
    public function get(array $skus)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $select = $this->attributeResource->getConnection()
            ->select()
            ->from($this->attributeResource->getTable($this->table));
        return $this->attributeResource->getConnection()->fetchAll(
            $select->where($this->getEntityLinkField() . ' IN (?)', $ids, \Zend_Db::INT_TYPE)
                ->where('attribute_id = ?', $this->getAttributeId())
        );
    }

    /**
     * Update prices.
     *
     * @param array $prices
     * @return void
     * @throws CouldNotSaveException
     */
    public function update(array $prices)
    {
        array_walk($prices, function (&$price) {
            return $price['attribute_id'] = $this->getAttributeId();
        });
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($prices, $this->itemsPerOperation) as $pricesBunch) {
                $this->attributeResource->getConnection()->insertOnDuplicate(
                    $this->attributeResource->getTable($this->table),
                    $pricesBunch,
                    ['value']
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new CouldNotSaveException(
                __('Could not save Prices.'),
                $e
            );
        }
    }

    /**
     * Delete product attribute by SKU.
     *
     * @param array $skus
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(array $skus)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->attributeResource->getConnection()->delete(
                    $this->attributeResource->getTable($this->table),
                    [
                        'attribute_id = ?' => $this->getAttributeId(),
                        $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                    ]
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new CouldNotDeleteException(
                __('Could not delete Prices'),
                $e
            );
        }
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $id
     * @param array $skus
     * @return int|null
     */
    public function retrieveSkuById($id, $skus)
    {
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            if (false !== array_key_exists($id, $ids)) {
                return $sku;
            }
        }

        return null;
    }

    /**
     * Get attribute ID.
     *
     * @return int
     */
    private function getAttributeId()
    {
        if (!$this->attributeId) {
            $this->attributeId = $this->attributeRepository->get($this->attributeCode)->getAttributeId();
        }

        return $this->attributeId;
    }

    /**
     * Retrieve affected product IDs.
     *
     * @param array $skus
     * @return array
     */
    private function retrieveAffectedIds(array $skus)
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productIds) {
            $affectedIds[] = array_keys($productIds);
        }

        return array_unique(array_merge([], ...$affectedIds));
    }

    /**
     * Get link field.
     *
     * @return string
     */
    public function getEntityLinkField()
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
    }

    /**
     * Update last updated date.
     *
     * @param array $productIds
     * @return void
     * @throws CouldNotSaveException
     */
    public function updateLastUpdatedAt(array $productIds): void
    {
        try {
            $this->productAction->updateAttributes(
                $productIds,
                [ProductInterface::UPDATED_AT => $this->dateTime->formatDate($this->coreDate->gmtDate())],
                Store::DEFAULT_STORE_ID
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The attribute can't be saved."),
                $e
            );
        }
    }
}
