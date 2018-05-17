<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Controller\Adminhtml\SourceSelection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * ProcessAlgorithm Controller
 */
class ProcessAlgorithm extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

    /**
     * @var StockByWebsiteIdResolverInterface
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
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @param Context $context
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        Context $context,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($context);
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
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

            //TODO: maybe need to add exception when websiteId empty
            $websiteId = $postRequest['websiteId'] ?? 1;
            $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

            $result = $requestItems = [];
            foreach ($requestData as $data) {
                $requestItems[] = $this->itemRequestFactory->create([
                    'sku' => $data['sku'],
                    'qty' => $data['qty']
                ]);
            }
            $inventoryRequest = $this->inventoryRequestFactory->create([
                'stockId' => $stockId,
                'items'   => $requestItems
            ]);

            $sourceSelectionResult = $this->sourceSelectionService->execute($inventoryRequest, $algorithmCode);

            foreach ($requestData as $data) {
                $orderItem = $data['orderItem'];
                foreach ($sourceSelectionResult->getSourceSelectionItems() as $item) {
                    if ($item->getSku() === $data['sku']) {
                        $result[$orderItem][] = [
                            'sourceName' => $this->getSourceName($item->getSourceCode()),
                            'sourceCode' => $item->getSourceCode(),
                            'qtyAvailable' => $item->getQtyAvailable(),
                            'qtyToDeduct' => $item->getQtyToDeduct()
                        ];
                    }
                }
            }

            foreach ($this->sources as $value => $label) {
                $result['sourceCodes'][] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
            $resultJson->setData($result);
        }

        return $resultJson;
    }

    /**
     * Get source name by code
     *
     * @param string $sourceCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceName(string $sourceCode): string
    {
        if (!isset($this->sources[$sourceCode])) {
            $this->sources[$sourceCode] = $this->sourceRepository->get($sourceCode)->getName();
        }

        return $this->sources[$sourceCode];
    }
}
