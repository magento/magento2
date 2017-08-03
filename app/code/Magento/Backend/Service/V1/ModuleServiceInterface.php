<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Service\V1;

/**
 * Interface for module service.
 * @api
 * @since 2.0.0
 */
interface ModuleServiceInterface
{
    /**
     * Returns an array of enabled modules
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getModules();
}
