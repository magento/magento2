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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * HTML select element block
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Block\Html;

class Date extends \Magento\Core\Block\Template
{
    protected function _toHtml()
    {
        $html  = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->escapeHtml($this->getValue()) . '" class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        $calendarYearsRange = $this->getYearsRange();
        $html .=
            '<script type="text/javascript">
            //<![CDATA[
            (function($) {
                $(document).ready(function(){
                    $("#' . $this->getId() . '").calendar({
                        showsTime: ' . ($this->getTimeFormat() ? 'true' : 'false') . ',
                        ' . ($this->getTimeFormat() ? ('timeFormat: "' . $this->getTimeFormat() . '",') : '') . '
                        dateFormat: "' . $this->getDateFormat() . '",
                        buttonImage: "' . $this->getImage() . '",
                        ' . ($calendarYearsRange ? 'yearRange: "' . $calendarYearsRange . '",' : '') . '
                        buttonText: "' . __('Select Date') . '"
                    })
                });
            })(jQuery)
            //]]>
            </script>';

        return $html;
    }

    public function getEscapedValue($index=null) {

        if($this->getFormat() && $this->getValue()) {
            return strftime($this->getFormat(), strtotime($this->getValue()));
        }

        return htmlspecialchars($this->getValue());
    }

    public function getHtml()
    {
        return $this->toHtml();
    }
}
