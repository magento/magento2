<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:disable PSR1.Files.SideEffects
namespace Magento\Framework\Error;

// phpcs:ignore Magento2.Functions.DiscouragedFunction,Magento2.Security.IncludeFile
require_once realpath(__DIR__) . '/../../app/bootstrap.php';
require_once 'processor.php'; // phpcs:ignore Magento2.Security.IncludeFile
use Magento\Framework\App\ObjectManager as AppObjectManager;

/**
 * Error processor factory
 */
class ProcessorFactory
{
    /**
     * Create Processor
     *
     * @return Processor
     */
    public function createProcessor()
    {
        try {
            $objectManager = AppObjectManager::getInstance();
            return $objectManager->create(Processor::class);
        } catch (\RuntimeException $exception) {
            // phpcs:ignore Magento2.Security.Superglobal
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
            $objectManager = $objectManagerFactory->create($_SERVER); // phpcs:ignore Magento2.Security.Superglobal
            $response = $objectManager->create(\Magento\Framework\App\Response\Http::class);
            return new Processor($response);
        }
    }
}
