<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Layout plugin that handle exceptions
 */
class Plugin
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param State $appState
     * @param Logger $logger
     */
    public function __construct(
        State $appState,
        Logger $logger
    ) {
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\View\Layout $subject
     * @param callable $proceed
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function aroundRenderNonCachedElement(\Magento\Framework\View\Layout $subject, \Closure $proceed, $name)
    {
        $result = '';
        try {
            $result = $proceed($name);
        } catch (\Exception $e) {
            if ($this->appState->getMode() === State::MODE_DEVELOPER) {
                throw $e;
            }
            $message = ($e instanceof LocalizedException) ? $e->getLogMessage() : $e->getMessage();
            $this->logger->critical($message);
        }
        return $result;
    }
}
