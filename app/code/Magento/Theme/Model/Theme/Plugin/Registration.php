<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Model\Theme\Registration as ThemeRegistration;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State as AppState;

class Registration
{
    /** @var ThemeRegistration */
    protected $themeRegistration;

    /** @var LoggerInterface */
    protected $logger;

    /** @var AppState */
    protected $appState;

    /**
     * @param ThemeRegistration $themeRegistration
     * @param LoggerInterface $logger
     * @param AppState $appState
     */
    public function __construct(
        ThemeRegistration $themeRegistration,
        LoggerInterface $logger,
        AppState $appState
    ) {
        $this->themeRegistration = $themeRegistration;
        $this->logger = $logger;
        $this->appState = $appState;
    }

    /**
     * Add new theme from filesystem
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        AbstractAction $subject,
        RequestInterface $request
    ) {
        try {
            if ($this->appState->getMode() != AppState::MODE_PRODUCTION) {
                $this->themeRegistration->register();
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}
