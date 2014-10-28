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
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Checkbox grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @return string
     */
    public function getHtml()
    {
        return '<span class="head-massaction">' . parent::getHtml() . '</span>';
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        return array(
            array('label' => __('Any'), 'value' => ''),
            array('label' => __('Yes'), 'value' => 1),
            array('label' => __('No'), 'value' => 0)
        );
    }

    /**
     * @return array
     */
    public function getCondition()
    {
        if ($this->getValue()) {
            return $this->getColumn()->getValue();
        } else {
            return array(array('neq' => $this->getColumn()->getValue()), array('is' => new \Zend_Db_Expr('NULL')));
        }
        // return array('like'=>'%'.$this->getValue().'%');
    }
}
