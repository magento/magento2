<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * System configuration shipping methods allow all countries select
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Block\System\Config\Form\Field\Select;

class Allowspecific extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Add additional Javascript code
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $javaScript = "\n            <script type=\"text/javascript\">require(['prototype'], function(){\n                Event.observe('{$this->getHtmlId()}', 'change', function(){\n                    specific=\$('{$this
            ->getHtmlId()}').value;\n                    \$('{$this
            ->_getSpecificCountryElementId()}').disabled = (!specific || specific!=1);\n                });\n            });</script>";
        return $javaScript . parent::getAfterElementHtml();
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        if (!$this->getValue() || 1 != $this->getValue()) {
            $element = $this->getForm()->getElement($this->_getSpecificCountryElementId());
            $element->setDisabled('disabled');
        }
        return parent::getHtml();
    }

    /**
     * @return string
     */
    protected function _getSpecificCountryElementId()
    {
        return substr($this->getId(), 0, strrpos($this->getId(), 'allowspecific')) . 'specificcountry';
    }
}
