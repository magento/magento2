<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Field renderer for hidden fields
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Hidden extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(Context $context, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($context, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Decorate field row html to be invisible
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" >' . $html . '</tr>' .
            /* @noEscape */ $this->secureRenderer->renderStyleAsTag(
                "display: none;",
                'tr#row_' . $element->getHtmlId()
            );
    }
}
