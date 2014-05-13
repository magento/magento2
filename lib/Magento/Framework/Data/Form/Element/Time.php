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
 * Form time element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Time extends AbstractElement
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
        $this->setType('time');
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }
        return $name;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('select');

        $value_hrs = 0;
        $value_min = 0;
        $value_sec = 0;

        if ($value = $this->getValue()) {
            $values = explode(',', $value);
            if (is_array($values) && count($values) == 3) {
                $value_hrs = $values[0];
                $value_min = $values[1];
                $value_sec = $values[2];
            }
        }

        $html = '<input type="hidden" id="' . $this->getHtmlId() . '" ' . $this->_getUiId() . '/>';
        $html .= '<select name="' . $this->getName() . '" style="width:50px" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId(
            'hour'
        ) . '>' . "\n";
        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_hrs ==
                $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";

        $html .= ':&nbsp;<select name="' . $this->getName() . '" style="width:50px" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId(
            'minute'
        ) . '>' . "\n";
        for ($i = 0; $i < 60; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_min ==
                $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";

        $html .= ':&nbsp;<select name="' . $this->getName() . '" style="width:50px" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId(
            'second'
        ) . '>' . "\n";
        for ($i = 0; $i < 60; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= '<option value="' . $hour . '" ' . ($value_sec ==
                $i ? 'selected="selected"' : '') . '>' . $hour . '</option>';
        }
        $html .= '</select>' . "\n";
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
