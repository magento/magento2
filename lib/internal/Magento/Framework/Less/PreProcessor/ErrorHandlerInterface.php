<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
