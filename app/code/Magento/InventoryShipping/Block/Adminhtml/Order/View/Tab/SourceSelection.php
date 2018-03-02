<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryShipping\Model\InventoryRequestFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class SourceSelection extends Template implements TabInterface
{
    /**
     * @var SourceSelectionResultInterface
     */
    private $sourceSelectionResult = null;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var InventoryRequestFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SourceRepositoryInterface $sourceRepository
     * @param InventoryRequestFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SourceRepositoryInterface $sourceRepository,
        InventoryRequestFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->sourceRepository = $sourceRepository;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
    }

    /**
     * Check if order items can be shipped by the current shipping algorithm
     *
     * @return bool
     */
    public function isShippable()
    {
        return $this->getShippingAlgorithmResult()->isShippable();
    }

    /**
     * Get source selections for order grouped by sourceCode
     *
     * @return array
     */
    public function getSourceSelections(): array
    {
        $result = [];
        foreach ($this->getShippingAlgorithmResult()->getSourceItemSelections() as $item) {
            $result[$item->getSourceCode()][] = $item;
        }
        return $result;
    }

    /**
     * Get source name by code
     *
     * @param $sourceCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceName(string $sourceCode): string
    {
        return $this->sourceRepository->get($sourceCode)->getName();
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Source Selection');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Source Selection');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->isShippable();
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return SourceSelectionResultInterface
     */
    private function getShippingAlgorithmResult()
    {
        if (null === $this->sourceSelectionResult) {
            $order = $this->registry->registry('current_order');
            $inventoryRequest = $this->inventoryRequestFactory->create($order);
            $this->sourceSelectionResult = $this->sourceSelectionService->execute($inventoryRequest);
        }

        return $this->sourceSelectionResult;
    }
}
