<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Magento\Framework\App\State;

/**
 * Oyejorge adapter model
 */
class Oyejorge implements \Magento\Framework\Css\PreProcessor\AdapterInterface
{
    const ERROR_MESSAGE_PREFIX = 'CSS compilation from LESS ';

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
        $options = ['relativeUrls' => false, 'compress' => $this->appState->getMode() !== State::MODE_DEVELOPER];
        try {
            $parser = new \Less_Parser($options);
            $parser->parseFile($sourceFilePath, '');
            return $parser->getCss();
        } catch (\Exception $e) {
            $errorMessage = self::ERROR_MESSAGE_PREFIX . $e->getMessage();
            $this->logger->critical($errorMessage);
            return $errorMessage;
        }
    }
}
