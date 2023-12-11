<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Grid\Renderer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Adminhtml customers wishlist grid item action renderer for few action controls in one cell
 */
class Multiaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var SecureHtmlRenderer
     */
    private $secureHtmlRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = [],
        ?SecureHtmlRenderer $secureHtmlRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct($context, $jsonEncoder, $data, $secureHtmlRenderer, $random);
        $this->secureHtmlRenderer = $secureHtmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
    }

    /**
     * Renders column
     *
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $actions = $this->getColumn()->getActions();
        if (!empty($actions) && is_array($actions)) {
            $links = [];
            foreach ($actions as $action) {
                if (is_array($action)) {
                    $link = $this->_toLinkHtml($action, $row);
                    if ($link) {
                        $links[] = $link;
                    }
                }
            }
            $html = implode('<br />', $links);
        }

        if ($html == '') {
            $html = '&nbsp;';
        }

        return $html;
    }

    /**
     * Render single action as link html
     *
     * @param  array $action
     * @param  \Magento\Framework\DataObject $row
     * @return string|false
     */
    protected function _toLinkHtml($action, \Magento\Framework\DataObject $row)
    {
        $product = $row->getProduct();

        if (isset($action['process']) && $action['process'] == 'configurable') {
            if ($product->canConfigure()) {
                $id = 'id' .$this->random->getRandomString(10);
                $onClick = sprintf('return %s.configureItem(%s)', $action['control_object'], $row->getId());
                return sprintf(
                    '<a href="%s" id="%s" class="configure-item-link">%s</a>%s',
                    $action['url'],
                    $id,
                    $action['caption'],
                    $this->secureHtmlRenderer->renderEventListenerAsTag('onclick', $onClick, "#$id")
                );
            } else {
                return false;
            }
        } else {
            return parent::_toLinkHtml($action, $row);
        }
    }
}
