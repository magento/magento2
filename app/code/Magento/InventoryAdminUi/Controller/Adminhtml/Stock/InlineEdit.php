<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * InlineEdit Controller
 */
class InlineEdit extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::stock';

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        DataObjectHelper $dataObjectHelper,
        StockRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $stockId = (int)$itemData[StockInterface::STOCK_ID];
                    $stock = $this->stockRepository->get($stockId);
                    $this->dataObjectHelper->populateWithArray($stock, $itemData, StockInterface::class);
                    $this->stockRepository->save($stock);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] The Stock does not exist.',
                        ['value' => $stockId]
                    );
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $localizedError) {
                        $errorMessages[] = __('[ID: %value] %message', [
                            'value' => $stockId,
                            'message' => $localizedError->getMessage()
                        ]);
                    }
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] = __('[ID: %value] %message', [
                        'value' => $stockId,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        } else {
            $errorMessages[] = __('Please correct the sent data.');
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
