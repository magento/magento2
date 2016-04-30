<?php
/**
 * Form Element Boolean Data Model
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

class Boolean extends Select
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
