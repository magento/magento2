<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Framework\EntityManager\EventManager;

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
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param StockInterfaceFactory $stockFactory
     * @param StockRepositoryInterface $stockRepository
     * @param StockSourceLinkProcessor $stockSourceLinkProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param EventManager $eventManager
     */
    public function __construct(
        StockInterfaceFactory $stockFactory,
        StockRepositoryInterface $stockRepository,
        StockSourceLinkProcessor $stockSourceLinkProcessor,
        DataObjectHelper $dataObjectHelper,
        EventManager $eventManager
    ) {
        $this->stockFactory = $stockFactory;
        $this->stockRepository = $stockRepository;
        $this->stockSourceLinkProcessor = $stockSourceLinkProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * Save stock process action
     *
     * @param int|null $stockId
     * @param RequestInterface $request
     *
     * @return int
     * @throws LocalizedException
     */
    public function process($stockId, RequestInterface $request): int
    {
        try {
            if (null === $stockId) {
                $stock = $this->stockFactory->create();
            } else {
                $stock = $this->stockRepository->get($stockId);
            }
            $requestData = $request->getParams();
            $this->dataObjectHelper->populateWithArray($stock, $requestData['general'], StockInterface::class);
            $this->eventManager->dispatch(
                'save_stock_controller_populate_stock_with_data',
                [
                    'request'   => $request,
                    'stock'     => $stock,
                ]
            );
            $stockId = $this->stockRepository->save($stock);
            $this->eventManager->dispatch(
                'save_stock_controller_processor_after_save',
                [
                    'request'   => $request,
                    'stock'     => $stock,
                ]
            );

            $assignedSources =
                isset($requestData['sources']['assigned_sources'])
                    && is_array($requestData['sources']['assigned_sources'])
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
