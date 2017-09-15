<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

/**
 * Introduces family of integration calls.
 * Each implementation represents call to external service.
 * @since 2.2.0
 */
interface CommandInterface
{
    /**
     * Execute call to external service
     * Information about destination and arguments appears from config
     *
     * @return bool
     * @since 2.2.0
     */
    public function execute();
}
