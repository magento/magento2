<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

use Magento\Framework\App\RequestInterface;

/**
 * Resolves whether a request may initially open a Redis session without a write lock.
 *
 * Read-only mode must be configured explicitly per path. It must not be inferred from
 * the HTTP method because Magento extensions can mutate session data on any method.
 */
class SessionAccessModeResolver
{
    /**
     * @param RequestInterface $request
     * @param string[] $readOnlyPaths
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly array $readOnlyPaths = []
    ) {
    }

    /**
     * Returns whether the request may initially read the session without a write lock.
     */
    public function isReadOnly(): bool
    {
        if (!method_exists($this->request, 'getPathInfo')) {
            return false;
        }

        return in_array(trim($this->request->getPathInfo(), '/'), $this->readOnlyPaths, true);
    }
}
