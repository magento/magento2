<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\App\State;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;

/**
 * Provider of template view files
 */
class TemplateFile extends File
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var MinifierInterface
     */
    protected $templateMinifier;

    /**
     * @var ConfigInterface
     */
    protected $assetConfig;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ResolverInterface $resolver
     * @param MinifierInterface $templateMinifier
     * @param State $appState
     * @param ConfigInterface $assetConfig
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ResolverInterface $resolver,
        MinifierInterface $templateMinifier,
        State $appState,
        ConfigInterface $assetConfig,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->appState = $appState;
        $this->templateMinifier = $templateMinifier;
        $this->assetConfig = $assetConfig;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        parent::__construct($resolver);
    }

    /**
     * @return string
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE;
    }

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string|bool
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $template = parent::getFile($area, $themeModel, $file, $module);

        if ($template && $this->assetConfig->isMinifyHtml()) {
            switch ($this->appState->getMode()) {
                case State::MODE_PRODUCTION:
                    return $this->getMinifiedTemplateInProduction($template);
                case State::MODE_DEFAULT:
                    return $this->templateMinifier->getMinified($template);
                case State::MODE_DEVELOPER:
                default:
                    return $template;
            }
        }
        return $template;
    }

    /**
     * Returns path to minified template file
     *
     * If SCD on demand in production is disabled - returns the path to minified template file.
     * Otherwise returns the path to minified template file,
     * or minify if file not exist and returns path.
     *
     * @param string $template
     * @return string
     */
    private function getMinifiedTemplateInProduction($template)
    {
        $forceMinification = $this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            || $this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_FORCE_HTML_MINIFICATION);

        return $forceMinification ?
            $this->templateMinifier->getMinified($template)
            : $this->templateMinifier->getPathToMinified($template);
    }
}
