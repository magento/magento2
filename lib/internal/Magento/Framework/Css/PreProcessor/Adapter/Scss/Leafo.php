<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter\Scss;

use Magento\Framework\App\State;

/**
 * Leafo SCSS adapter
 */
class Leafo implements \Magento\Framework\Css\PreProcessor\AdapterInterface
{
    const ERROR_MESSAGE_PREFIX = 'CSS compilation from SCSS ';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param State $appState
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        State $appState
    ) {
        $this->logger = $logger;
        $this->appState = $appState;
    }

    /**
     * @param string $sourceFilePath
     * @return string
     */
    public function process($sourceFilePath)
    {
        try {
            $compiler = new \scssc();
            $content = file_get_contents($sourceFilePath);
            return $compiler->compile($content);
        } catch (\Exception $e) {
            $errorMessage = self::ERROR_MESSAGE_PREFIX . $e->getMessage();
            $this->logger->critical($errorMessage);
            return $errorMessage;
        }
    }
}
