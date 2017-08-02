<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Config\ViewFactory;

/**
 * Handles theme view.xml files
 * @since 2.0.0
 */
class Config implements \Magento\Framework\View\ConfigInterface
{
    /**
     * List of view configuration objects per theme
     *
     * @var array
     * @since 2.0.0
     */
    protected $viewConfigs = [];

    /**
     * View service
     *
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $assetRepo;

    /**
     * File view factory
     *
     * @var \Magento\Framework\Config\ViewFactory
     * @since 2.0.0
     */
    protected $viewConfigFactory;

    /**
     * Constructor
     *
     * @param Asset\Repository $assetRepo
     * @param \Magento\Framework\Config\ViewFactory $viewConfigFactory
     * @since 2.0.0
     */
    public function __construct(
        Repository $assetRepo,
        ViewFactory $viewConfigFactory
    ) {
        $this->assetRepo = $assetRepo;
        $this->viewConfigFactory = $viewConfigFactory;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return \Magento\Framework\Config\View
     * @since 2.0.0
     */
    public function getViewConfig(array $params = [])
    {
        $this->assetRepo->updateDesignParams($params);
        $viewConfigParams = [];

        if (isset($params['themeModel'])) {
            /** @var \Magento\Framework\View\Design\ThemeInterface $currentTheme */
            $currentTheme = $params['themeModel'];
            $key = $currentTheme->getFullPath();
            if (isset($this->viewConfigs[$key])) {
                return $this->viewConfigs[$key];
            }
            $viewConfigParams['themeModel'] = $currentTheme;
        }
        $viewConfigParams['area'] = (isset($params['area'])) ? $params['area'] : null;

        /** @var \Magento\Framework\Config\View $config */
        $config = $this->viewConfigFactory->create($viewConfigParams);

        if (isset($key)) {
            $this->viewConfigs[$key] = $config;
        }
        return $config;
    }
}
