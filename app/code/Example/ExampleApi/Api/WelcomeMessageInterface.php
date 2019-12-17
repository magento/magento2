<?php
 /**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Example\ExampleApi\Api;

/**
 * Interface WelcomeMessageInreface
 *
 * @api
 */
interface WelcomeMessageInterface
{
    /**
     * Get welcome message
     *
     * @return string
     */
    public function execute(): string;
}
