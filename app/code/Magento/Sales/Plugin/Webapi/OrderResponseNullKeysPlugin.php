<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Webapi;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class OrderResponseNullKeysPlugin
{
    /**
     * Ensure state/status keys exist as null for order responses, so REST includes them.
     *
     * @param ServiceOutputProcessor $subject
     * @param mixed $result
     * @param mixed $data
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return array|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        ServiceOutputProcessor $subject,
        $result,
        $data,
        string $serviceClassName,
        string $serviceMethodName
    ) {
        if ($serviceClassName !== OrderRepositoryInterface::class) {
            return $result;
        }

        if ($serviceMethodName === 'get' && is_array($result)) {
            return $this->ensureOrderKeys($result);
        }

        if ($serviceMethodName === 'getList' &&
            is_array($result) &&
            isset($result['items'])
            && is_array($result['items'])
        ) {
            foreach ($result['items'] as $i => $item) {
                if (is_array($item)) {
                    $result['items'][$i] = $this->ensureOrderKeys($item);
                }
            }
        }

        return $result;
    }

    /**
     * If state and status key missing then set as null
     *
     * @param array $order
     * @return array
     */
    private function ensureOrderKeys(array $order): array
    {
        if (!array_key_exists('state', $order)) {
            $order['state'] = null;
        }
        if (!array_key_exists('status', $order)) {
            $order['status'] = null;
        }
        return $order;
    }
}
