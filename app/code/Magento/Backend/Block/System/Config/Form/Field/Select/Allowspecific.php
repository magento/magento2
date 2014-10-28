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


/**
 * System configuration shipping methods allow all countries select
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\System\Config\Form\Field\Select;

class Allowspecific extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Add additional Javascript code
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $javaScript = "\n            <script type=\"text/javascript\">\n                Event.observe('{$this->getHtmlId()}', 'change', function(){\n                    specific=\$('{$this
            ->getHtmlId()}').value;\n                    \$('{$this
            ->_getSpecificCountryElementId()}').disabled = (!specific || specific!=1);\n                });\n            </script>";
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
