<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\PageCache;

/**
 * Page unique identifier interface
 */
interface IdentifierInterface
{
    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue();
}
