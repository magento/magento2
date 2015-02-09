<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Template\Html\MinifierInterface;

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
     * @param ResolverInterface $resolver
     * @param MinifierInterface $templateMinifier
     * @param State $appState
     */
    public function __construct(
        ResolverInterface $resolver,
        MinifierInterface $templateMinifier,
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

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string|false
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $template = parent::getFile($area, $themeModel, $file, $module);
        var_dump(get_class($this->appState));
        var_dump($this->appState->getMode());
        switch ($this->appState->getMode()) {
            case State::MODE_PRODUCTION:
                return $this->templateMinifier->getPathToMinified($template);
                break;
            case State::MODE_DEFAULT:
                return $this->templateMinifier->getMinified($template);
                break;
            case State::MODE_DEVELOPER:
                return $template;
                break;
        }
    }
}
