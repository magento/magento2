<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Mode\Data;

use Magento\Csp\Api\Data\ModeConfiguredInterface;

/**
 * @inheritDoc
 */
class ModeConfigured implements ModeConfiguredInterface
{
    /**
     * @var bool
     */
    private $reportOnly;

    /**
     * @var string|null
     */
    private $reportUri;

    /**
     * @param bool $reportOnly
     * @param string|null $reportUri
     */
    public function __construct(bool $reportOnly, ?string $reportUri)
    {
        $this->reportOnly = $reportOnly;
        $this->reportUri = $reportUri;
    }

    /**
     * @inheritDoc
     */
    public function isReportOnly(): bool
    {
        return $this->reportOnly;
    }

    /**
     * @inheritDoc
     */
    public function getReportUri(): ?string
    {
        return $this->reportUri;
    }
}
