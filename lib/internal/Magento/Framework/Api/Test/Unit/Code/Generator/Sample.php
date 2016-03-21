<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

/**
 * Class Sample for Proxy and Factory generation
 */
class Sample
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
