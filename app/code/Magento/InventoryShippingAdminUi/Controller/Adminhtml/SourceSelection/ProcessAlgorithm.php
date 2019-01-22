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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrder;

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
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @var GetInventoryRequestFromOrder
     */
    private $getInventoryRequestFromOrder;

    /**
     * ProcessAlgorithm constructor.
     *
     * @param Context $context
     * @param null $stockByWebsiteIdResolver @deprecated
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param null $inventoryRequestFactory @deprecated
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetInventoryRequestFromOrder|null $getInventoryRequestFromOrder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestFactory,
        $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceRepositoryInterface $sourceRepository,
        GetInventoryRequestFromOrder $getInventoryRequestFromOrder = null
    ) {
        parent::__construct($context);
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->getInventoryRequestFromOrder = $getInventoryRequestFromOrder ?:
            ObjectManager::getInstance()->get(GetInventoryRequestFromOrder::class);
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
            $requestItems[] = $this->itemRequestFactory->create([
                'sku' => $data['sku'],
                'qty' => $data['qty']
            ]);
        }

        return $requestItems;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
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
            $requestItems = $this->getRequestItems($requestData);

            $inventoryRequest = $this->getInventoryRequestFromOrder->execute($orderId, $requestItems);
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
