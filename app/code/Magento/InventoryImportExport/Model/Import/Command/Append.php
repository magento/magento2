<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;

/**
 * @inheritdoc
 */
class Append implements CommandInterface
{
    /**
     * @var SourceItemConvert
     */
    private $sourceItemConvert;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param SourceItemConvert $sourceItemConvert
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        SourceItemConvert $sourceItemConvert,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->sourceItemConvert = $sourceItemConvert;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $bunch)
    {
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->debug('time start ' . time());
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->debug('bunch-size ' . count($bunch));

        $sourceItems = $this->sourceItemConvert->convert($bunch);
        $this->sourceItemsSave->execute($sourceItems);
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->debug('memory ' . $this->getNiceFileSize(memory_get_usage()));

        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->debug('time end ' . time());
    }

    function getNiceFileSize($bytes, $binaryPrefix=true) {
        if ($binaryPrefix) {
            $unit=array('B','KiB','MiB','GiB','TiB','PiB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        } else {
            $unit=array('B','KB','MB','GB','TB','PB');
            if ($bytes==0) return '0 ' . $unit[0];
            return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
        }
    }
}
