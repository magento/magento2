<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment;

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

    /** @var \Magento\Framework\Stdlib\DateTime */
    private $dateTime;

    /**
     * @var string
     */
    private $cachedValue;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param Version\StorageInterface $versionStorage
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\View\Deployment\Version\StorageInterface $versionStorage,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->appState = $appState;
        $this->versionStorage = $versionStorage;
        $this->dateTime = $dateTime;
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
        switch ($appMode) {
            case \Magento\Framework\App\State::MODE_DEFAULT:
                try {
                    $result = $this->versionStorage->load();
                } catch (\UnexpectedValueException $e) {
                    $result = $this->dateTime->toTimestamp(true);
                    $this->versionStorage->save($result);
                }
                break;

            case \Magento\Framework\App\State::MODE_DEVELOPER:
                $result = $this->dateTime->toTimestamp(true);
                break;

            default:
                $result = $this->versionStorage->load();
        }
        return $result;
    }
}
