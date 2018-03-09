<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\Algorithm;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;

/**
 * GetSources Controller | used ONLY for TEST.
 */
class GetSources extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * GetSources constructor.
     * @param Context $context
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     */
    public function __construct(
        Context $context,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    ) {
        parent::__construct($context);
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        /** @var Page $result */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $request = $this->getRequest();
        $postRequest = $request->getPost()->toArray();

        if ($request->isPost() && !empty($postRequest['requestData'])) {
            $requestData = $postRequest['requestData'];
            $defaultCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
            $algorithmCode = !empty($postRequest['algorithmCode']) ? $postRequest['algorithmCode'] : $defaultCode;

            $websiteId = $postRequest['websiteId'] ?? 1;
            $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

            $result = [];
            foreach ($requestData as $data) {
                $orderItem = $data['orderItem'];
                $requestItem = $this->itemRequestFactory->create([
                    'sku' => $data['sku'],
                    'qty' => $data['qty']
                ]);
                $inventoryRequest = $this->inventoryRequestFactory->create([
                    'stockId' => $stockId,
                    'items' => [$requestItem]
                ]);
                $sourceSelectionResult = $this->sourceSelectionService->execute(
                    $inventoryRequest,
                    $algorithmCode
                );
                foreach ($sourceSelectionResult->getSourceSelectionItems() as $item) {
                    $result[$orderItem][] = [
                        'sourceCode' => $item->getSourceCode(),
                        'qtyAvailable' => $item->getQtyAvailable(),
                        'qtyToDeduct' => $item->getQtyToDeduct()
                    ];
                }
            }
            $resultJson->setData($result);
        }
        return $resultJson;
    }
}
