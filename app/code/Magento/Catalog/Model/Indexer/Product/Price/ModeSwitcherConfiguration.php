<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Indexer\Model\Indexer;

/**
 * Class to configure indexers and system config after modes has been switched
 */
class ModeSwitcherConfiguration
{
    const XML_PATH_PRICE_DIMENSIONS_MODE = 'indexer/catalog_product_price/dimensions_mode';

    /**
     * ConfigInterface
     *
     * @var ConfigInterface
     */
    private $configWriter;

    /**
     * TypeListInterface
     *
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Indexer $indexer
     */
    private $indexer;

    /**
     * @param ConfigInterface   $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param Indexer           $indexer
     */
    public function __construct(
        ConfigInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Indexer $indexer
    ) {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->indexer = $indexer;
    }

    /**
     * Save switcher mode and invalidate reindex.
     *
     * @param string $mode
     * @return void
     * @throws \InvalidArgumentException
     */
    public function saveMode(string $mode)
    {
        //Change config options
        $this->configWriter->saveConfig(self::XML_PATH_PRICE_DIMENSIONS_MODE, $mode);
        $this->cacheTypeList->cleanType('config');
        $this->indexer->load(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
        $this->indexer->invalidate();
    }
}
