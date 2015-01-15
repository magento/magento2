<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

class Exception extends \Exception
{
    /**
     * Check PCRE PREG error and throw exception
     *
     * @return void
     * @throws \Magento\Framework\Exception
     */
    public static function processPcreError()
    {
        if (preg_last_error() != PREG_NO_ERROR) {
            switch (preg_last_error()) {
                case PREG_INTERNAL_ERROR:
                    throw new \Magento\Framework\Exception('PCRE PREG internal error');
                case PREG_BACKTRACK_LIMIT_ERROR:
                    throw new \Magento\Framework\Exception('PCRE PREG Backtrack limit error');
                case PREG_RECURSION_LIMIT_ERROR:
                    throw new \Magento\Framework\Exception('PCRE PREG Recursion limit error');
                case PREG_BAD_UTF8_ERROR:
                    throw new \Magento\Framework\Exception('PCRE PREG Bad UTF-8 error');
                case PREG_BAD_UTF8_OFFSET_ERROR:
                    throw new \Magento\Framework\Exception('PCRE PREG Bad UTF-8 offset error');
            }
        }
    }
}
