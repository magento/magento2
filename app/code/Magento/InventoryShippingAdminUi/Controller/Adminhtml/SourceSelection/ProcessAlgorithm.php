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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Exception\UndefinedInventoryRequestBuilderException;
use Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrderBuilder;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * ProcessAlgorithm Controller
 */
class ProcessAlgorithm extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

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
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetInventoryRequestFromOrderBuilder
     */
    private $getInventoryRequestFromOrderBuilder;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestInterfaceFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * ProcessAlgorithm constructor.
     *
     * @param Context $context
     * @param ItemRequestInterfaceFactory $itemRequestInterfaceFactory
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetInventoryRequestFromOrderBuilder $getInventoryRequestFromOrderBuilder
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceRepositoryInterface $sourceRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        ItemRequestInterfaceFactory $itemRequestInterfaceFactory,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetInventoryRequestFromOrderBuilder $getInventoryRequestFromOrderBuilder,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceRepositoryInterface $sourceRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getInventoryRequestFromOrderBuilder = $getInventoryRequestFromOrderBuilder;
        $this->itemRequestInterfaceFactory = $itemRequestInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get request items
     *
     * @param array $requestData
     * @return array
     */
    private function getRequestItems(array $requestData): array
    {
        $requestItems = [];
        foreach ($requestData as $data) {
            $requestItems[] = $this->itemRequestInterfaceFactory->create([
                'sku' => $data['sku'],
                'qty' => $data['qty']
            ]);
        }

        return $requestItems;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     * @throws UndefinedInventoryRequestBuilderException
     */
    public function execute(): ResultInterface
    {
        /** @var Page $result */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $request = $this->getRequest();
        $postRequest = $request->getPost()->toArray();
        $orderId = (int) $postRequest['orderId'];

        if (!empty($postRequest['requestData'])) {
            $requestData = $postRequest['requestData'];
            $defaultCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
            $algorithmCode = !empty($postRequest['algorithmCode']) ? $postRequest['algorithmCode'] : $defaultCode;

            //TODO: maybe need to add exception when websiteId empty
            $websiteId = (int) $postRequest['websiteId'] ?? 1;
            $stockId = (int) $this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();

            $requestItems = $this->getRequestItems($requestData);

            $order = $this->orderRepository->get($orderId);

            $inventoryRequestBuilder = $this->getInventoryRequestFromOrderBuilder->execute($algorithmCode);
            $inventoryRequest = $inventoryRequestBuilder->execute($stockId, $order, $requestItems);

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
     * @throws NoSuchEntityException
     */
    public function getSourceName(string $sourceCode): string
    {
        if (!isset($this->sources[$sourceCode])) {
            $this->sources[$sourceCode] = $this->sourceRepository->get($sourceCode)->getName();
        }

        return $this->sources[$sourceCode];
    }
}
