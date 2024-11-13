<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Model\Connector;

/**
 * Introduces family of integration calls.
 * Each implementation represents call to external service.
 *
 * @api
 */
interface CommandInterface
{
    /**
     * Execute call to external service
     * Information about destination and arguments appears from config
     *
     * @return bool
     */
    public function execute();
}
