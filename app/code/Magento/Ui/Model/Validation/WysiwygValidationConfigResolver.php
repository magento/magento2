<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\Validation;

use Magento\Ui\Model\ResourceModel\Utf8mb4SupportInterface;

class WysiwygValidationConfigResolver
{
    /**
     * @var Utf8mb4SupportInterface
     */
    private Utf8mb4SupportInterface $utf8mb4Support;

    /**
     * @var array<string, bool>
     */
    private array $supportCache = [];

    /**
     * @param Utf8mb4SupportInterface $utf8mb4Support
     */
    public function __construct(Utf8mb4SupportInterface $utf8mb4Support)
    {
        $this->utf8mb4Support = $utf8mb4Support;
    }

    /**
     * Resolve whether utf8mb4 should be allowed for the current field.
     *
     * @param array $config
     * @return bool
     */
    public function resolveAllowUtf8mb4(array $config): bool
    {
        $allowUtf8mb4 = $config['allowUtf8mb4'] ?? $config['wysiwygConfigData']['allowUtf8mb4'] ?? null;

        if (is_bool($allowUtf8mb4)) {
            return $allowUtf8mb4;
        }

        $target = $this->resolveTarget($config);

        if ($target === null) {
            return false;
        }

        $cacheKey = $target['table'] . '.' . $target['column'];

        if (!array_key_exists($cacheKey, $this->supportCache)) {
            $this->supportCache[$cacheKey] = $this->utf8mb4Support->isColumnSupported(
                $target['table'],
                $target['column']
            );
        }

        return $this->supportCache[$cacheKey];
    }

    /**
     * Resolve the storage target for the current field.
     *
     * @param array $config
     * @return array{table: string, column: string}|null
     */
    private function resolveTarget(array $config): ?array
    {
        $target = $config['utf8mb4Target'] ?? $config['wysiwygConfigData']['utf8mb4Target'] ?? null;

        if (!is_array($target)) {
            return null;
        }

        $table = is_string($target['table'] ?? null) ? $target['table'] : '';
        $column = is_string($target['column'] ?? null) ? $target['column'] : '';

        if ($table === '' || $column === '') {
            return null;
        }

        return [
            'table' => $table,
            'column' => $column,
        ];
    }
}
