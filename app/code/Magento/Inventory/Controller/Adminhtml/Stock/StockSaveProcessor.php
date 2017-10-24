<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * Save stock processor for save stock controller
 */
class StockSaveProcessor
{
    /**
     * @var StockInterfaceFactory
     */
    private $stockFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockSourceLinkProcessor
     */
    private $stockSourceLinkProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param StockInterfaceFactory $stockFactory
     * @param StockRepositoryInterface $stockRepository
     * @param StockSourceLinkProcessor $stockSourceLinkProcessor
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        StockInterfaceFactory $stockFactory,
        StockRepositoryInterface $stockRepository,
        StockSourceLinkProcessor $stockSourceLinkProcessor,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->stockFactory = $stockFactory;
        $this->stockRepository = $stockRepository;
        $this->stockSourceLinkProcessor = $stockSourceLinkProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Save stock process action
     *
     * @param int|null $stockId
     * @param array $requestData
     *
     * @return int
     * @throws LocalizedException
     * @throws ValidationException
     */
    public function process($stockId, array $requestData): int
    {
        try {
            if (null === $stockId) {
                $stock = $this->stockFactory->create();
            } else {
                $stock = $this->stockRepository->get($stockId);
            }
            $this->dataObjectHelper->populateWithArray($stock, $requestData['general'], StockInterface::class);
            $stockId = $this->stockRepository->save($stock);

            $assignedSources =
                isset($requestData['sources']['assigned_sources']) && is_array($requestData['sources']['assigned_sources'])
                    ? $requestData['sources']['assigned_sources']
                    : [];
            $this->stockSourceLinkProcessor->process($stockId, $assignedSources);

            return $stockId;
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The Stock does not exist.'));
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__($e->getMessage()));
        } catch (InputException $e) {
            throw new LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not save stock.'));
        }
    }
}
