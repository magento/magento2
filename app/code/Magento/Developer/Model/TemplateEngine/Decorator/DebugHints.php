<?php
/**
 * Decorator that inserts debugging hints into the rendered block contents
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\TemplateEngine\Decorator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Decorates block with block and template hints
 *
 * @api
 * @since 100.0.2
 */
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
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param \Magento\Framework\View\TemplateEngineInterface $subject
     * @param bool $showBlockHints Whether to include block into the debugging information or not
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        \Magento\Framework\View\TemplateEngineInterface $subject,
        $showBlockHints,
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        $this->_subject = $subject;
        $this->_showBlockHints = $showBlockHints;
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Insert debugging hints into the rendered block contents
     *
     * Insert debugging hints into the rendered block contents
     * @inheritdoc
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
        $hintsId = 'hintsId_' .$this->random->getRandomString(32);
        $hintsTemplateFileId = 'hintsTemplateFileId_' .$this->random->getRandomString(32);

        $scriptString = <<<HTML
<div class="debugging-hints" id="{$hintsId}">
<div class="debugging-hint-template-file" id="{$hintsTemplateFileId}" title="{$templateFile}">{$templateFile}</div>
{$blockHtml}
</div>
HTML;

        return $scriptString .
            $this->secureRenderer->renderStyleAsTag(
                "position: relative; border: 1px dotted red; margin: 6px 2px; padding: 18px 2px 2px 2px;",
                '#' . $hintsId
            ) . $this->secureRenderer->renderStyleAsTag(
                "position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; left: 0;" .
                " color: white; white-space: nowrap;",
                '#' . $hintsTemplateFileId
            ) . $this->secureRenderer->renderEventListenerAsTag(
                'onmouseover',
                "this.style.zIndex = 999;",
                '#' . $hintsTemplateFileId
            ) . $this->secureRenderer->renderEventListenerAsTag(
                'onmouseout',
                "this.style.zIndex = 'auto';",
                '#' . $hintsTemplateFileId
            );
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
        $hintsId = 'hintsBlockId_' .$this->random->getRandomString(32);
        $scriptString = <<<HTML
<div class="debugging-hint-block-class" id="{$hintsId}" title="{$blockClass}">{$blockClass}</div>
{$blockHtml}
HTML;

        return $scriptString .
            $this->secureRenderer->renderStyleAsTag(
                "position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; right: 0;" .
                " color: blue; white-space: nowrap;",
                '#' . $hintsId
            ) . $this->secureRenderer->renderEventListenerAsTag(
                'onmouseover',
                "this.style.zIndex = 999;",
                '#' . $hintsId
            ) . $this->secureRenderer->renderEventListenerAsTag(
                'onmouseout',
                "this.style.zIndex = 'auto';",
                '#' . $hintsId
            );
    }
}
