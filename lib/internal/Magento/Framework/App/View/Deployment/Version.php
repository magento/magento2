<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment;

use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * Deployment version of static files
 */
class Version
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $versionStorage;

    /**
     * @var string
     */
    private $cachedValue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param Version\StorageInterface $versionStorage
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\View\Deployment\Version\StorageInterface $versionStorage
    ) {
        $this->appState = $appState;
        $this->versionStorage = $versionStorage;
    }

    /**
     * Retrieve deployment version of static files
     *
     * @return string
     */
    public function getValue()
    {
        if (!$this->cachedValue) {
            $this->cachedValue = $this->readValue($this->appState->getMode());
        }
        return $this->cachedValue;
    }

    /**
     * Load or generate deployment version of static files depending on the application mode
     *
     * @param string $appMode
     * @return string
     */
    protected function readValue($appMode)
    {
        if ($appMode == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $result = $this->generateVersion();
        } else {
            try {
                $result = $this->versionStorage->load();
            } catch (\UnexpectedValueException $e) {
                $result = $this->generateVersion();
                if ($appMode == \Magento\Framework\App\State::MODE_DEFAULT) {
                    try {
                        $this->versionStorage->save($result);
                    } catch (FileSystemException $e) {
                        $this->getLogger()->critical('Can not save static content version.');
                    }
                } else {
                    $this->getLogger()->critical('Can not load static content version.');
                }
            }
        }
        return $result;
    }

    /**
     * Generate version of static content
     *
     * @return int
     */
    private function generateVersion()
    {
        return time();
    }

    /**
     * Get logger
     *
     * @return LoggerInterface
     * @deprecated
     */
    private function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(LoggerInterface::class);
        }
        return $this->logger;
    }
}
