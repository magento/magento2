<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code\Generator;

class Sample
{
    private $attribute;

    public function getValue()
    {
        return $this->attribute;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->attribute = $value;
    }

    public function & getReference()
    {
    }
}
