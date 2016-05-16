<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute\Data;

/**
 * EAV Entity Attribute Boolean Data Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Boolean extends \Magento\Eav\Model\Attribute\Data\Select
{
    /**
     * Return a text for option value
     *
     * @param int $value
     * @return string
     */
    protected function _getOptionText($value)
    {
        switch ($value) {
            case '0':
                $text = __('No');
                break;
            case '1':
                $text = __('Yes');
                break;
            default:
                $text = '';
                break;
        }
        return $text;
    }
}
