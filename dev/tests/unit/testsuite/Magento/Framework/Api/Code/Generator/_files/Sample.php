<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Api\Code\Generator;

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
