<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * InlineEdit Controller
 */
class InlineEdit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param Context $context
     * @param HydratorInterface $hydrator
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        HydratorInterface $hydrator,
        StockRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->hydrator = $hydrator;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $stock = $this->stockRepository->get(
                        $itemData[StockInterface::STOCK_ID]
                    );
                    $stock = $this->hydrator->hydrate($stock, $itemData);
                    $this->stockRepository->save($stock);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __(
                        '[ID: %1] The Stock does not exist.',
                        $itemData[StockInterface::STOCK_ID]
                    );
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] =
                        __('[ID: %1] ', $itemData[StockInterface::STOCK_ID])
                        . $e->getMessage();
                }
            }
        } else {
            $errorMessages[] = __('Please correct the data sent.');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => count($errorMessages),
        ]);
        return $resultJson;
    }
}
