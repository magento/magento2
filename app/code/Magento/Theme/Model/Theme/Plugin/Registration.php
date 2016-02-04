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
use Magento\Framework\App\State as AppState;
use Magento\Theme\Model\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeLoader;
use Magento\Framework\Config\Theme;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Registration
{
    /** @var ThemeRegistration */
    protected $themeRegistration;

    /** @var ThemeCollection */
    protected $themeCollection;

    /** @var ThemeLoader */
    protected $themeLoader;

    /** @var LoggerInterface */
    protected $logger;

    /** @var AppState */
    protected $appState;

    /**
     * @param ThemeRegistration $themeRegistration
     * @param ThemeCollection $themeCollection
     * @param ThemeLoader $themeLoader
     * @param LoggerInterface $logger
     * @param AppState $appState
     */
    public function __construct(
        ThemeRegistration $themeRegistration,
        ThemeCollection $themeCollection,
        ThemeLoader $themeLoader,
        LoggerInterface $logger,
        AppState $appState
    ) {
        $this->themeRegistration = $themeRegistration;
        $this->themeCollection = $themeCollection;
        $this->themeLoader = $themeLoader;
        $this->logger = $logger;
        $this->appState = $appState;
    }

    /**
     * Add new theme from filesystem and update existing
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
                $this->updateThemeData();
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Update theme data
     *
     * @return void
     */
    protected function updateThemeData()
    {
        $themesData = $this->themeCollection->loadData();
        /** @var \Magento\Theme\Model\Theme $themeData */
        foreach ($themesData as $themeData) {
            if ($themeData->getParentTheme()) {
                $parentTheme = $this->themeLoader->getThemeByFullPath(
                    $themeData->getParentTheme()->getFullPath()
                );
                $themeData->setParentId($parentTheme->getId());
            }

            /** @var \Magento\Theme\Model\Theme $theme */
            $theme = $this->themeLoader->getThemeByFullPath(
                $themeData->getArea()
                . Theme::THEME_PATH_SEPARATOR
                . $themeData->getThemePath()
            );
            $theme->addData($themeData->toArray())->save();
        }
    }
}
