<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\PreProcessor;

/**
 * Error handler interface
 *
 * @api
 */
interface ErrorHandlerInterface
{
    /**
     * Process an exception which was thrown during processing dynamic instructions
     *
     * @param \Exception $e
     * @return void
     */
    public function processException(\Exception $e);
}
