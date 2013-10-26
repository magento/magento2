<?php
/**
 * Decorator that inserts debugging hints into the rendered block contents
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\TemplateEngine\Decorator;

class DebugHints implements \Magento\Core\Model\TemplateEngine\EngineInterface
{
    /**
     * @var \Magento\Core\Model\TemplateEngine\EngineInterface
     */
    private $_subject;

    /**
     * @var bool
     */
    private $_showBlockHints;

    /**
     * @param \Magento\Core\Model\TemplateEngine\EngineInterface $subject
     * @param bool $showBlockHints Whether to include block into the debugging information or not
     */
    public function __construct(
        \Magento\Core\Model\TemplateEngine\EngineInterface $subject,
        $showBlockHints
    ) {
        $this->_subject = $subject;
        $this->_showBlockHints = $showBlockHints;
    }

    /**
     * Insert debugging hints into the rendered block contents
     *
     * {@inheritdoc}
     */
    public function render(\Magento\Core\Block\Template $block, $templateFile, array $dictionary = array())
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
<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'"
onmouseout="this.style.zIndex='998'" title="{$templateFile}">{$templateFile}</div>
{$blockHtml}
</div>
HTML;
    }

    /**
     * Insert block debugging hints into the rendered block contents
     *
     * @param string $blockHtml
     * @param \Magento\Core\Block\Template $block
     * @return string
     */
    protected function _renderBlockHints($blockHtml, \Magento\Core\Block\Template $block)
    {
        $blockClass = get_class($block);
        return <<<HTML
<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'" onmouseout="this.style.zIndex='998'"
title="{$blockClass}">{$blockClass}</div>
{$blockHtml}
HTML;
    }
}
