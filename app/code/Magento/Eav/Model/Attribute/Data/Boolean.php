<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Attribute\Data;

/**
 * EAV Entity Attribute Boolean Data Model
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
