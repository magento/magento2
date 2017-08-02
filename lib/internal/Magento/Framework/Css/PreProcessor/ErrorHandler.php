<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

/**
 * Default Error Handler for CSS files pre-processing
 * @since 2.0.0
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function processException(\Exception $e)
    {
        $this->logger->critical($e);
    }
}
