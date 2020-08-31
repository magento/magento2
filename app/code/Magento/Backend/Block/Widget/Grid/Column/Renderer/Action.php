<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Grid column widget for rendering action grid cells
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureHtmlRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     * @param Random|null $random
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = [],
        ?SecureHtmlRenderer $secureHtmlRenderer = null,
        ?Random $random = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
        $this->secureHtmlRenderer = $secureHtmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
    }

    /**
     * Renders column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if (count($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $out = '<select class="admin__control-select" onchange="varienGridAction.execute(this);">' .
            '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            if (is_array($action)) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

    /**
     * Render single action as dropdown option html
     *
     * @param array $action
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _toOptionHtml($action, \Magento\Framework\DataObject $row)
    {
        $actionAttributes = new \Magento\Framework\DataObject();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        $htmlAttributes = [
            'value' => $this->escapeHtmlAttr($this->_jsonEncoder->encode($action), false)
        ];
        $actionAttributes->setData($htmlAttributes);
        return '<option ' . $actionAttributes->serialize() . '>' . $actionCaption . '</option>';
    }

    /**
     * Render single action as link html
     *
     * @param array $action
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _toLinkHtml($action, \Magento\Framework\DataObject $row)
    {
        $actionAttributes = new \Magento\Framework\DataObject();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if (isset($action['confirm'])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $action['onclick'] = 'return window.confirm(\'' . addslashes(
                $this->escapeHtml($action['confirm'])
            ) . '\')';
            unset($action['confirm']);
        }

        if (empty($action['id'])) {
            $action['id'] = 'id' .$this->random->getRandomString(10);
        }
        $actionAttributes->setData($action);
        $onclick = $actionAttributes->getData('onclick');
        $style = $actionAttributes->getData('style');
        $actionAttributes->unsetData(['onclick', 'style']);
        $html = '<a ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
        if ($onclick) {
            $html .= $this->secureHtmlRenderer->renderEventListenerAsTag('onclick', $onclick, "#{$action['id']}");
        }
        if ($style) {
            $html .= $this->secureHtmlRenderer->renderStyleAsTag($style, "#{$action['id']}");
        }

        return $html;
    }

    /**
     * Prepares action data for html render
     *
     * @param &array $action
     * @param &string $actionCaption
     * @param \Magento\Framework\DataObject $row
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _transformActionData(&$action, &$actionCaption, \Magento\Framework\DataObject $row)
    {
        foreach ($action as $attribute => $value) {
            if (isset($action[$attribute]) && !is_array($action[$attribute])) {
                $this->getColumn()->setFormat($action[$attribute]);
                $action[$attribute] = parent::render($row);
            } else {
                $this->getColumn()->setFormat(null);
            }

            switch ($attribute) {
                case 'caption':
                    $actionCaption = $action['caption'];
                    unset($action['caption']);
                    break;

                case 'url':
                    if (is_array($action['url']) && isset($action['field'])) {
                        $params = [$action['field'] => $this->_getValue($row)];
                        if (isset($action['url']['params'])) {
                            $params[] = $action['url']['params'];
                        }
                        $action['href'] = $this->getUrl($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }
                    unset($action['url']);
                    break;

                case 'popup':
                    $action['onclick'] = 'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,'
                        . 'scrollbars=1\');return false;';
                    break;
            }
        }
        return $this;
    }
}
