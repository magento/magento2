<?php
/**
 * Decorator that inserts debugging hints into the rendered block contents
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Developer\Model\TemplateEngine\Decorator;

class DebugHints implements \Magento\Framework\View\TemplateEngineInterface
{
    /**
     * @var \Magento\Framework\View\TemplateEngineInterface
     */
    private $_subject;

    /**
     * @var bool
     */
    private $_showBlockHints;

    /**
     * @param \Magento\Framework\View\TemplateEngineInterface $subject
     * @param bool $showBlockHints Whether to include block into the debugging information or not
     */
    public function __construct(\Magento\Framework\View\TemplateEngineInterface $subject, $showBlockHints)
    {
        $this->_subject = $subject;
        $this->_showBlockHints = $showBlockHints;
    }

    /**
     * Insert debugging hints into the rendered block contents
     *
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\View\Element\BlockInterface $block, $templateFile, array $dictionary = [])
    {
        $result = $this->_subject->render($block, $templateFile, $dictionary);
        if ($this->_showBlockHints) {
            $result = $this->_renderBlockHints($result, $block);
        }
        $result = $this->_renderTemplateHints($result, $templateFile);
        return $result;
    }

    /**
     * Insert template debugging hints into the rendered block contents
     *
     * @param string $blockHtml
     * @param string $templateFile
     * @return string
     */
    protected function _renderTemplateHints($blockHtml, $templateFile)
    {
        return <<<HTML
<div class="debugging-hints" style="position: relative; border: 1px dotted red; margin: 6px 2px; padding: 18px 2px 2px 2px;">
<div class="debugging-hint-template-file" style="position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; left: 0; color: white; white-space: nowrap;" onmouseover="this.style.zIndex = 999;" onmouseout="this.style.zIndex = 'auto';" title="{$templateFile}">{$templateFile}</div>
{$blockHtml}
</div>
HTML;
    }

    /**
     * Insert block debugging hints into the rendered block contents
     *
     * @param string $blockHtml
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @return string
     */
    protected function _renderBlockHints($blockHtml, \Magento\Framework\View\Element\BlockInterface $block)
    {
        $blockClass = get_class($block);
        return <<<HTML
<div class="debugging-hint-block-class" style="position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; right: 0; color: blue; white-space: nowrap;" onmouseover="this.style.zIndex = 999;" onmouseout="this.style.zIndex = 'auto';" title="{$blockClass}">{$blockClass}</div>
{$blockHtml}
HTML;
    }
}
