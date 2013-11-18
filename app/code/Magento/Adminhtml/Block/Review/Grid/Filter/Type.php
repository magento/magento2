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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml review grid filter by type
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Adminhtml\Block\Review\Grid\Filter;

class Type extends \Magento\Adminhtml\Block\Widget\Grid\Column\Filter\Select
{
    protected function _getOptions()
    {
        return array(
              array('label'=>'', 'value'=>''),
              array('label'=>__('Administrator'), 'value'=>1),
              array('label'=>__('Customer'), 'value'=>2),
              array('label'=>__('Guest'), 'value'=>3)
        );
    }

    public function getCondition()
    {
        if ($this->getValue() == 1) {
            return 1;
        } elseif ($this->getValue() == 2) {
            return 2;
        } else {
            return 3;
        }
    }
}// Class \Magento\Adminhtml\Block\Review\Grid\Filter\Type END
