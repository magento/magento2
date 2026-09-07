<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\ResourceModel;

interface Utf8mb4SupportInterface
{
    /**
     * Determine if a specific storage target can safely store utf8mb4 characters.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function isColumnSupported(string $table, string $column): bool;
}
