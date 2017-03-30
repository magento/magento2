<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

/**
 * Error handler interface
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
