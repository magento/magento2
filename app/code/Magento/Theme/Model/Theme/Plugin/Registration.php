<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Model\Theme\Registration as ThemeRegistration;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Registration
{
    /** @var ThemeRegistration */
    protected $themeRegistration;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param ThemeRegistration $themeRegistration
     * @param LoggerInterface $logger
     */
    public function __construct(
        ThemeRegistration $themeRegistration,
        LoggerInterface $logger
    ) {
        $this->themeRegistration = $themeRegistration;
        $this->logger = $logger;
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
            $this->themeRegistration->register();
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}
