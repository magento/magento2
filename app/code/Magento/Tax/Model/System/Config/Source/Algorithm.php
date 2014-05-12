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
namespace Magento\Tax\Model\System\Config\Source;

class Algorithm implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Initialize the options array
     */
    public function __construct()
    {
        $this->_options = array(
            array('value' => \Magento\Tax\Model\Calculation::CALC_UNIT_BASE, 'label' => __('Unit Price')),
            array('value' => \Magento\Tax\Model\Calculation::CALC_ROW_BASE, 'label' => __('Row Total')),
            array('value' => \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE, 'label' => __('Total'))
        );
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_options;
    }
}
