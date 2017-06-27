<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Email;

/**
 * @api
 */
class Smtpauth implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'NONE', 'label' => 'NONE'],
            ['value' => 'PLAIN', 'label' => 'PLAIN'],
            ['value' => 'LOGIN', 'label' => 'LOGIN'],
            ['value' => 'CRAM-MD5', 'label' => 'CRAM-MD5']
        ];
    }
}
