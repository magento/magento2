<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Hint adds "Configuration Details" link to payment configuration.
 * `<comment>` node must be defined in `<group>` node and contain some link.
 */
class Hint extends Template implements RendererInterface
{
    /**
     * @var string
     * @deprecated 2.1.3
     */
    protected $_template = 'Magento_Paypal::system/config/fieldset/hint.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '';

        if ($element->getComment()) {
            $html .= sprintf('<tr id="row_%s">', $element->getHtmlId());
            $html .= '<td colspan="1"><p class="note"><span>';
            $html .= sprintf(
                '<a href="%s" target="_blank">Configuration Details</a>',
                $element->getComment()
            );
            $html .= '</span></p></td></tr>';
        }

        return $html;
    }
}
