<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Csp\Api\Data\ModeConfiguredInterface;

/**
 * CSP mode config manager.
 *
 * Responsible for CSP mode configurations like report-only/restrict modes, report URL etc.
 *
 * @api
 */
interface ModeConfigManagerInterface
{
    /**
     * Load CSP mode config.
     *
     * @return ModeConfiguredInterface
     * @throws \RuntimeException When failed to retrieve configurations.
     */
    public function getConfigured(): ModeConfiguredInterface;
}
