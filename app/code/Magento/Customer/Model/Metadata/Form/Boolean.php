<?php
/**
 * Form Element Boolean Data Model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
