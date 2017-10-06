<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Design\Fallback\RulePool as ViewDesignFallbackRulePool;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Store\Model\ScopeInterface;

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
     * TemplateFile constructor.
     *
     * @param ResolverInterface $resolver
     * @param MinifierInterface $templateMinifier
     * @param State $appState
     * @param ConfigInterface $assetConfig
     */
    public function __construct(
        ResolverInterface $resolver,
        MinifierInterface $templateMinifier,
        State $appState,
        ConfigInterface $assetConfig
    ) {
        $this->appState = $appState;
        $this->templateMinifier = $templateMinifier;
        $this->assetConfig = $assetConfig;

        parent::__construct($resolver);
    }

    /**
     * @return string
     */
    protected function getFallbackType()
    {
        return ViewDesignFallbackRulePool::TYPE_TEMPLATE_FILE;
    }

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     *
     * @return string|bool
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $scopeType = (AppArea::AREA_ADMINHTML == $area) ?
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT :
            ScopeInterface::SCOPE_STORE;
        $template = parent::getFile($area, $themeModel, $file, $module);

        if ($template && $this->assetConfig->isMinifyHtml($scopeType)) {
            switch ($this->appState->getMode()) {
                case State::MODE_PRODUCTION:
                    return $this->templateMinifier->getPathToMinified($template);
                case State::MODE_DEFAULT:
                    return $this->templateMinifier->getMinified($template);
                case State::MODE_DEVELOPER:
                default:
                    return $template;
            }
        }

        return $template;
    }
}
