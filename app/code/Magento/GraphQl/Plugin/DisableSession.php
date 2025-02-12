<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Plugin;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionStartChecker;
use Magento\GraphQl\Model\Config\DisableSession as DisableSessionConfig;

/**
 * Disable session in graphql area if configured.
 */
class DisableSession
{
    /**
     * @var DisableSessionConfig
     */
    private $disableSessionConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param DisableSessionConfig $disableSessionConfig
     * @param State $appState
     */
    public function __construct(
        DisableSessionConfig $disableSessionConfig,
        State $appState
    ) {
        $this->disableSessionConfig = $disableSessionConfig;
        $this->appState = $appState;
    }

    /**
     * Prevents session starting while in graphql area and session is disabled in config.
     *
     * @param SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        if (!$result) {
            return false;
        }
        try {
            if ($this->appState->getAreaCode() === Area::AREA_GRAPHQL && $this->disableSessionConfig->isDisabled()) {
                $result = false;
            }
        } catch (LocalizedException $e) {} finally { //@codingStandardsIgnoreLine
            return $result;
        }
    }
}
