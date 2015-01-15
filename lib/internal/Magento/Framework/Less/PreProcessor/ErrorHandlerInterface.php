<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Less\PreProcessor;

/**
 * Error handler interface
 */
interface ErrorHandlerInterface
{
    /**
     * Process an exception which was thrown during processing a less instructions
     *
     * @param \Exception $e
     * @return void
     */
    public function processException(\Exception $e);
}
