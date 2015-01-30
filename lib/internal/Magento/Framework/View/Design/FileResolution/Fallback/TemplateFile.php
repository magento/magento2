<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;
use Magento\Framework\App\State;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Template\Html\Minifier;

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
     * @var Minifier
     */
    protected $templateMinifier;

    /**
     * @param ResolverInterface $resolver
     * @param Minifier $templateMinifier
     * @param State $appState
     */
    public function __construct(
        ResolverInterface $resolver,
        Minifier $templateMinifier,
        State $appState
    ) {
        $this->appState = $appState;
        $this->templateMinifier = $templateMinifier;
        parent::__construct($resolver);
    }

    /**
     * @return string
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE;
    }

    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $template = parent::getFile($area, $themeModel, $file, $module);
        switch ($this->appState->getMode()) {
            case State::MODE_PRODUCTION:
                return $this->templateMinifier->getNewFilePath($template);
                break;
            case State::MODE_DEFAULT:
                return $this->templateMinifier->getMinifyFile($template);
                break;
            case State::MODE_DEVELOPER:
                return $template;
                break;
        }
    }
}
