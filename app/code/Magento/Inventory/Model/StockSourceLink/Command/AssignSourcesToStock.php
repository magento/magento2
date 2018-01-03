<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterfaceFactory;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class AssignSourcesToStock implements AssignSourcesToStockInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StockSourceLinksSaveInterface
     */
    private $stockSourceLinksSave;

    /**
     * @var StockSourceLinkInterfaceFactory
     */
    private $stockSourceLinkFactory;

    /**
     * @param StockSourceLinkInterfaceFactory $stockSourceLinkFactory
     * @param StockSourceLinksSaveInterface $stockSourceLinksSave
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockSourceLinkInterfaceFactory $stockSourceLinkFactory,
        StockSourceLinksSaveInterface $stockSourceLinksSave,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->stockSourceLinksSave = $stockSourceLinksSave;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceCodes, int $stockId)
    {
        if (empty($sourceCodes)) {
            throw new InputException(__('Input data is invalid'));
        }

        try {
            $this->stockSourceLinksSave->execute($this->getStockSourceLinks($sourceCodes, $stockId));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not assign Sources to Stock'), $e);
        }
    }

    /**
     * Map link information to StockSourceLinkInterface multiply
     *
     * @param array $sourceCodeList
     * @param int $stockId
     *
     * @return StockSourceLinkInterface[]
     */
    private function getStockSourceLinks(array $sourceCodeList, $stockId): array
    {
        $linkList = [];

        foreach ($sourceCodeList as $sourceCode) {
            /** @var StockSourceLinkInterface $linkData */
            $linkData = $this->stockSourceLinkFactory->create();

            $linkData->setSourceCode($sourceCode);
            $linkData->setStockId($stockId);

            $linkList[] = $linkData;
        }

        return $linkList;
    }
}
