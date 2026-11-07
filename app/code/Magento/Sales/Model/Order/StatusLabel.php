<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StatusLabel is responsible for retrieving order status labels based on store of order
 */
class StatusLabel implements ResetAfterRequestInterface
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
    private $statusLabels = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StatusFactory $orderStatusFactory
     * @param State $state
     * @param array $maskStatusesMapping
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        StatusFactory $orderStatusFactory,
        State $state,
        array $maskStatusesMapping = [],
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->orderStatusFactory = $orderStatusFactory;
        $this->state = $state;
        $this->maskStatusesMapping = $maskStatusesMapping;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Get status label for a specified area
     *
     * @param string|null $code
     * @param string $area
     * @param int|null $storeId
     * @return string|null
     */
    public function getStatusFrontendLabel(?string $code, string $area, ?int $storeId = null): ?string
    {
        $code = $this->maskStatusForArea($area, $code);
        $storeId = $area === Area::AREA_ADMINHTML ? 0 : (int)$this->storeManager->getStore($storeId)->getId();
        if (!array_key_exists($code, $this->statusLabels[$area][$storeId] ?? [])) {
            $status = $this->orderStatusFactory->create()->load($code);
            $storeLabels = $area === Area::AREA_ADMINHTML ? [] : $status->getStoreLabels();
            $hasStoreLabel = isset($storeLabels[$storeId]);
            $this->statusLabels[$area][$storeId][$code] = [
                'label' => $hasStoreLabel ? (string)$storeLabels[$storeId] : $status->getLabel(),
                'translate' => $area !== Area::AREA_ADMINHTML && !$hasStoreLabel,
            ];
        }

        $label = $this->statusLabels[$area][$storeId][$code];
        return $label['translate'] ? (string)__($label['label']) : $label['label'];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->clearCache();
    }

    /**
     * Clear cached order status labels.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->statusLabels = [];
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
        $code = $code ?? '';
        if (isset($this->maskStatusesMapping[$area][$code])) {
            return $this->maskStatusesMapping[$area][$code];
        }
        return $code;
    }

    /**
     * Retrieve status label for detected area
     *
     * @param string|null $code
     * @return string|null
     */
    public function getStatusLabel($code)
    {
        $area = $this->state->getAreaCode() ?: Area::AREA_FRONTEND;
        return $this->getStatusFrontendLabel($code, $area);
    }
}
