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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form multiline text elements
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Multiline extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = array()
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('text');
        $this->setLineCount(2);
    }

    /**
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'maxlength');
    }

    /**
     * @param int $suffix
     * @return string
     */
    public function getLabelHtml($suffix = 0)
    {
        return parent::getLabelHtml($suffix);
    }

    /**
     * Get element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $lineCount = $this->getLineCount();

        for ($i = 0; $i < $lineCount; $i++) {
            if ($i == 0 && $this->getRequired()) {
                $this->setClass('input-text required-entry');
            } else {
                $this->setClass('input-text');
            }
            $html .= '<div class="multi-input"><input id="' .
                $this->getHtmlId() .
                $i .
                '" name="' .
                $this->getName() .
                '[' .
                $i .
                ']' .
                '" value="' .
                $this->getEscapedValue(
                    $i
                ) . '" ' . $this->serialize(
                    $this->getHtmlAttributes()
                ) . '  ' . $this->_getUiId(
                    $i
                ) . '/>' . "\n";
            if ($i == 0) {
                $html .= $this->getAfterElementHtml();
            }
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * @return mixed
     */
    public function getDefaultHtml()
    {
        $html = '';
        $lineCount = $this->getLineCount();

        for ($i = 0; $i < $lineCount; $i++) {
            $html .= $this->getNoSpan() === true ? '' : '<span class="field-row">' . "\n";
            if ($i == 0) {
                $html .= '<label for="' .
                    $this->getHtmlId() .
                    $i .
                    '">' .
                    $this->getLabel() .
                    ($this->getRequired() ? ' <span class="required">*</span>' : '') .
                    '</label>' .
                    "\n";
                if ($this->getRequired()) {
                    $this->setClass('input-text required-entry');
                }
            } else {
                $this->setClass('input-text');
                $html .= '<label>&nbsp;</label>' . "\n";
            }
            $html .= '<input id="' .
                $this->getHtmlId() .
                $i .
                '" name="' .
                $this->getName() .
                '[' .
                $i .
                ']' .
                '" value="' .
                $this->getEscapedValue(
                    $i
                ) . '"' . $this->serialize(
                    $this->getHtmlAttributes()
                ) . ' />' . "\n";
            if ($i == 0) {
                $html .= $this->getAfterElementHtml();
            }
            $html .= $this->getNoSpan() === true ? '' : '</span>' . "\n";
        }
        return $html;
    }
}
