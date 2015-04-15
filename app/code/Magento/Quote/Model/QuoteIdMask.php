<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

/**
 * QuoteIdMask model
 *
 * @method string getMaskedId()
 * @method QuoteIdMask setMaskedId()
 */
class QuoteIdMask extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\Resource\Quote\QuoteIdMask');
    }

    /**
     * Initialize quote identifier before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->setMaskedId($this->guidv4());
        return $this;
    }

    /**
     * Generate guid
     *
     * Utility to generate random guid style identifiers
     * Original Source: http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
     *
     * @return string
     */
    protected function guidv4()
    {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
