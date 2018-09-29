<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Block\Adminhtml\Shipment;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Display selected source on shipment creation page
 *
 * @api
 */
class Inventory extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * Inventory constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SourceRepositoryInterface $sourceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->registry->registry('current_shipment');
    }

    /**
     * Retrieve source code from shipment
     *
     * @return null|string
     */
    public function getSourceCode()
    {
        $shipment = $this->getShipment();
        $extensionAttributes = $shipment->getExtensionAttributes();
        if ($sourceCode = $extensionAttributes->getSourceCode()) {
            return $sourceCode;
        }
        return null;
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
}
