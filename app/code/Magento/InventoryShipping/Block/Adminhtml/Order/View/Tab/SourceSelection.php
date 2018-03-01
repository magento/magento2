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
use Magento\InventoryShipping\Model\ShippingAlgorithmProviderInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterface;

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class SourceSelection extends Template implements TabInterface
{
    /**
     * @var ShippingAlgorithmResultInterface
     */
    private $shippingAlgorithmResult;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ShippingAlgorithmProviderInterface
     */
    private $shippingAlgorithmProvider;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ShippingAlgorithmProviderInterface $shippingAlgorithmProvider
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ShippingAlgorithmProviderInterface $shippingAlgorithmProvider,
        SourceRepositoryInterface $sourceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->shippingAlgorithmProvider = $shippingAlgorithmProvider;
        $this->sourceRepository = $sourceRepository;
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
     * Get source selections for order
     *
     * @return SourceSelectionInterface[]
     */
    public function getSourceSelections(): array
    {
        return $this->getShippingAlgorithmResult()->getSourceSelections();
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
     * @return ShippingAlgorithmResultInterface
     */
    private function getShippingAlgorithmResult()
    {
        if (null === $this->shippingAlgorithmResult) {
            $order = $this->registry->registry('current_order');
            $shippingAlgorithm = $this->shippingAlgorithmProvider->execute();

            $this->shippingAlgorithmResult = $shippingAlgorithm->execute($order);
        }

        return $this->shippingAlgorithmResult;
    }
}
