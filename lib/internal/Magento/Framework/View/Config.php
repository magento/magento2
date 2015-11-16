<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Config\ViewFactory;

/**
 * Handles theme view.xml files
 */
class Config implements \Magento\Framework\View\ConfigInterface
{
    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $viewConfigs = [];

    /**
     * View service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * File view factory
     *
     * @var \Magento\Framework\Config\ViewFactory
     */
    protected $viewConfigFactory;

    /**
     * Constructor
     *
     * @param Asset\Repository $assetRepo
     * @param \Magento\Framework\Config\ViewFactory $viewConfigFactory
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
     */
    public function getViewConfig(array $params = [])
    {
        $this->assetRepo->updateDesignParams($params);
        /** @var $currentTheme \Magento\Framework\View\Design\ThemeInterface */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getCode();
        if (isset($this->viewConfigs[$key])) {
            return $this->viewConfigs[$key];
        }

        $config = $this->viewConfigFactory->create();

        $this->viewConfigs[$key] = $config;
        return $config;
    }
}
