<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api\Data;

/**
 * CSP mode.
 *
 * @api
 */
interface ModeConfiguredInterface
{
    /**
     * Report only mode flag.
     *
     * In "report-only" mode browsers only report violation but do not restrict them.
     *
     * @return bool
     */
    public function isReportOnly(): bool;

    /**
     * URI of endpoint logging reported violations.
     *
     * Even in "restrict" mode violations can be logged.
     *
     * @return string|null
     */
    public function getReportUri(): ?string;
}
