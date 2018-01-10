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
use Magento\InventoryShipping\Model\ShippingAlgorithmInterface;
use Magento\InventoryShipping\Model\SourceSelectionInterface;

/**
 * Tab for source items display on the order editing page
 *
 * @api
 */
class SourceSelection extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ShippingAlgorithmInterface
     */
    private $shippingAlgorithm;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ShippingAlgorithmInterface $shippingAlgorithm
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ShippingAlgorithmInterface $shippingAlgorithm,
        SourceRepositoryInterface $sourceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->shippingAlgorithm = $shippingAlgorithm;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Get source selections for order
     */
    public function getSourceSelections()
    {
        $order = $this->registry->registry('current_order');
        return $this->shippingAlgorithm->execute($order)->getSourceSelections();
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
