<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\CustomerData;

use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Catalog\Model\ProductFrontendAction;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Config;
use Psr\Log\LoggerInterface;

/**
 * Generates Product Frontend Action Section in Customer Data
 */
class ProductFrontendActionSection implements SectionSourceInterface
{
    /**
     * Identification of Type of a Product Frontend Action
     *
     * @var string
     */
    private $typeId;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @param Synchronizer $synchronizer
     * @param string $typeId Identification of Type of a Product Frontend Action
     * @param LoggerInterface $logger
     * @param Config $appConfig
     */
    public function __construct(
        Synchronizer $synchronizer,
        $typeId,
        LoggerInterface $logger,
        Config $appConfig
    ) {
        $this->typeId = $typeId;
        $this->synchronizer = $synchronizer;
        $this->logger = $logger;
        $this->appConfig = $appConfig;
    }

    /**
     * Post Process collection data in order to eject all customer sensitive information
     *
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!(bool) $this->appConfig->getValue(Synchronizer::ALLOW_SYNC_WITH_BACKEND_PATH)) {
            return [
                'count' => 0,
                'items' => [],
            ];
        }

        $actions = $this->synchronizer->getActionsByType($this->typeId);
        $items = [];

        /** @var ProductFrontendAction $action */
        foreach ($actions as $action) {
            $items[$action->getProductId()] = [
                'added_at' => $action->getAddedAt(),
                'product_id' => $action->getProductId(),
            ];
        }

        return [
            'count' => count($items),
            'items' => $items,
        ];
    }
}
