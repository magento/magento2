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

/**
 * Provider of template view files
 * @since 2.0.0
 */
class TemplateFile extends File
{
    /**
     * @var State
     * @since 2.0.0
     */
    protected $appState;

    /**
     * @var MinifierInterface
     * @since 2.0.0
     */
    protected $templateMinifier;

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $assetConfig;

    /**
     * @param ResolverInterface $resolver
     * @param MinifierInterface $templateMinifier
     * @param State $appState
     * @param ConfigInterface $assetConfig
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $template = parent::getFile($area, $themeModel, $file, $module);

        if ($template && $this->assetConfig->isMinifyHtml()) {
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
