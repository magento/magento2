<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;

/**
 * Triggers on order cancellation and tries to process Signifyd case cancellation
 * if it available.
 */
class CancelOrder implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CancelGuaranteeAbility
     */
    private $guaranteeAbility;

    /**
     * @var GuaranteeCancelingServiceInterface
     */
    private $cancelingService;

    /**
     * @param Config $config
     * @param CancelGuaranteeAbility $guaranteeAbility
     * @param GuaranteeCancelingServiceInterface $cancelingService
     */
    public function __construct(
        Config $config,
        CancelGuaranteeAbility $guaranteeAbility,
        GuaranteeCancelingServiceInterface $cancelingService
    ) {

        $this->config = $config;
        $this->guaranteeAbility = $guaranteeAbility;
        $this->cancelingService = $cancelingService;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $observer->getEvent()
            ->getDataByKey('order');

        if ($order === null) {
            return;
        }

        if ($this->guaranteeAbility->isAvailable($order->getEntityId())) {
            $this->cancelingService->cancelForOrder($order->getEntityId());
        }
    }
}
