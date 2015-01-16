<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Email;

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
