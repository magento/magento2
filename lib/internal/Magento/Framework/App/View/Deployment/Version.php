<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment;

use Psr\Log\LoggerInterface;

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
        $result = $this->versionStorage->load();
        if (!$result) {
            if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION) {
                $this->getLogger()->critical('Can not load static content version.');
                throw new \UnexpectedValueException(
                    "Unable to retrieve deployment version of static files from the file system."
                );
            }
            $result = $this->generateVersion();
            $this->versionStorage->save($result);
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
     * @deprecated 100.2.0
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
