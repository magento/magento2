<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Customer new password field renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Newpass extends AbstractBlock implements RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<div class="field field-' . $element->getHtmlId() . '">';
        $html .= $element->getLabelHtml();
        $html .= '<div class="control">' . $element->getElementHtml();
        $html .= '<div class="nested">';
        $html .= '<div class="field choice">';
        $html .= '<label for="account-send-pass" class="addbefore"><span>' . __('or ') . '</span></label>';
        $html .= '<input type="checkbox" id="account-send-pass" name="' .
            $element->getName() .
            '" value="auto" onclick="setElementDisable(\'' .
            $element->getHtmlId() .
            '\', this.checked)" />';
        $html .= '<label class="label" for="account-send-pass"><span>' . __(
            ' Send auto-generated password'
        ) . '</span></label>';
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";

        return $html;
    }
}
