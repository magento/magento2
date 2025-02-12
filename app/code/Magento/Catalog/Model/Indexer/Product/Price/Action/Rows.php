<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Rows reindex action for mass actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) to preserve compatibility with parent class
 */
class Rows extends \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
{
    /**
     * Default batch size
     */
    private const BATCH_SIZE = 100;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var CacheContext
     */
    private CacheContext $cacheContext;

    /**
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param Type $catalogProductType
     * @param Factory $indexerPriceFactory
     * @param DefaultPrice $defaultIndexerResource
     * @param TierPrice|null $tierPriceIndexResource
     * @param DimensionCollectionFactory|null $dimensionCollectionFactory
     * @param TableMaintainer|null $tableMaintainer
     * @param int|null $batchSize
     * @param CacheContext|null $cacheContext
     * @SuppressWarnings(PHPMD.NPathComplexity) Added to backward compatibility with abstract class
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) Added to backward compatibility with abstract class
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) Added to backward compatibility with abstract class
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        Type $catalogProductType,
        Factory $indexerPriceFactory,
        DefaultPrice $defaultIndexerResource,
        ?TierPrice $tierPriceIndexResource = null,
        ?DimensionCollectionFactory $dimensionCollectionFactory = null,
        ?TableMaintainer $tableMaintainer = null,
        ?int $batchSize = null,
        ?CacheContext $cacheContext = null
    ) {
        parent::__construct(
            $config,
            $storeManager,
            $currencyFactory,
            $localeDate,
            $dateTime,
            $catalogProductType,
            $indexerPriceFactory,
            $defaultIndexerResource,
            $tierPriceIndexResource,
            $dimensionCollectionFactory,
            $tableMaintainer
        );
        $this->batchSize = $batchSize ?? self::BATCH_SIZE;
        $this->cacheContext = $cacheContext ?? ObjectManager::getInstance()->get(CacheContext::class);
    }

    /**
     * Execute Rows reindex
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\InputException(__('Bad value was supplied.'));
        }
        $currentBatch = [];
        $i = 0;

        foreach ($ids as $id) {
            $currentBatch[] = $id;
            if (++$i === $this->batchSize) {
                try {
                    $this->cacheContext->registerEntities(Product::CACHE_TAG, $this->_reindexRows($currentBatch));
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
                }
                $i = 0;
                $currentBatch = [];
            }
        }

        if (!empty($currentBatch)) {
            try {
                $this->cacheContext->registerEntities(Product::CACHE_TAG, $this->_reindexRows($currentBatch));
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
            }
        }
    }
}
