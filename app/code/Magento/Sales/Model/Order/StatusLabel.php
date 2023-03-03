<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Sales\Model\Order\StatusFactory;

/**
 * Class StatusLabel is responsible for retrieving order status labels based on store of order
 */
class StatusLabel
{
    /**
     * @var StatusFactory
     */
    private $orderStatusFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var array
     */
    private $maskStatusesMapping;

    /**
     * @var array
     */
    private static $statusLabels = [];

    /**
     * @param StatusFactory $orderStatusFactory
     * @param State $state
     * @param array $maskStatusesMapping
     */
    public function __construct(
        StatusFactory $orderStatusFactory,
        State $state,
        array $maskStatusesMapping = []
    ) {
        $this->orderStatusFactory = $orderStatusFactory;
        $this->state = $state;
        $this->maskStatusesMapping = $maskStatusesMapping;
    }

    /**
     * Get status label for a specified area
     *
     * @param string|null $code
     * @param string $area
     * @param int|null $storeId
     * @return string|null
     */
    public function getStatusFrontendLabel(?string $code, string $area, int $storeId = null): ?string
    {
        $cacheKey = $code . $area . ($storeId??'0');
        if (!isset(self::$statusLabels[$cacheKey])) {
            $code = $this->maskStatusForArea($area, $code);
            $status = $this->orderStatusFactory->create()->load($code);

            if ($area === Area::AREA_ADMINHTML) {
                self::$statusLabels[$cacheKey] = (string) $status->getLabel();
            } else {
                self::$statusLabels[$cacheKey] = (string) $status->getStoreLabel($storeId);
            }
        }
        return self::$statusLabels[$cacheKey];
    }

    /**
     * Mask status for order for specified area
     *
     * @param string $area
     * @param string|null $code
     * @return string|null
     */
    public function maskStatusForArea(string $area, ?string $code): ?string
    {
        if (isset($this->maskStatusesMapping[$area][$code])) {
            return $this->maskStatusesMapping[$area][$code];
        }
        return $code;
    }

    /**
     * Retrieve status label for detected area
     *
     * @param string $code
     * @return string|null
     */
    public function getStatusLabel($code)
    {
        $area = $this->state->getAreaCode() ?: Area::AREA_FRONTEND;
        return $this->getStatusFrontendLabel($code, $area);
    }
}
