<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Price\BasePriceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\DateTime;

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
     * Date time property to get the gm date.
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
     * @param DateTime|null $dateTime
     * @param BasePriceFactory|null $basePriceFactory
     */
    public function __construct(
        Attribute $attributeResource,
        ProductAttributeRepositoryInterface $attributeRepository,
        ProductIdLocatorInterface $productIdLocator,
        MetadataPool $metadataPool,
        $attributeCode = '',
        ?DateTime $dateTime = null,
        private ?BasePriceFactory $basePriceFactory = null
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->attributeCode = $attributeCode;
        $this->productIdLocator = $productIdLocator;
        $this->metadataPool = $metadataPool;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()
            ->get(DateTime::class);
        $this->basePriceFactory = $this->basePriceFactory ?: ObjectManager::getInstance()
            ->get(BasePriceFactory::class);
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
            return $price['attribute_id'] = (int)$this->getAttributeId();
        });

        $basePrice = $this->basePriceFactory->create(['attributeId' => (int)$this->getAttributeId()]);
        try {
            $basePrice->update($prices);
        } catch (\Exception $e) {
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
            $this->attributeResource->getConnection()->update(
                $this->attributeResource->getTable('catalog_product_entity'),
                [ProductInterface::UPDATED_AT => $this->dateTime->gmtDate()],
                [$this->getEntityLinkField(). ' IN(?)' => $productIds]
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The attribute can't be saved."),
                $e
            );
        }
    }
}
