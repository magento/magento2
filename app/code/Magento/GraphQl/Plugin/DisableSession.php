<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Plugin;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionStartChecker;
use Magento\GraphQl\Model\Config\DisableSession as DisableSessionConfig;

/**
 * Disable sessions for GraphQL GET requests or when configured.
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
     * @var Http
     */
    private $request;

    /**
     * @param DisableSessionConfig $disableSessionConfig
     * @param State $appState
     * @param Http|null $request
     */
    public function __construct(
        DisableSessionConfig $disableSessionConfig,
        State $appState,
        ?Http $request = null
    ) {
        $this->disableSessionConfig = $disableSessionConfig;
        $this->appState = $appState;
        $this->request = $request ?? ObjectManager::getInstance()->get(Http::class);
    }

    /**
     * Prevents sessions from starting for GraphQL GET requests or when disabled in config.
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
            if ($this->appState->getAreaCode() === Area::AREA_GRAPHQL
                && ($this->request->isGet() || $this->disableSessionConfig->isDisabled())
            ) {
                $result = false;
            }
        } catch (LocalizedException $e) {} finally { //@codingStandardsIgnoreLine
            return $result;
        }
    }
}
