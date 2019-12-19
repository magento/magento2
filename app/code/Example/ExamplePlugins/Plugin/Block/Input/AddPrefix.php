<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExamplePlugins\Plugin\Block\Input;

/**
 * Class modifies message
 * @package Example\ExamplePlugins\Plugin\Block\Input
 */
class AddPrefix
{
    /**
     * Add "prefix_"
     * @param $result
     * @return void
     */
    public function beforeGetMessageData($result)
    {
        $message = "prefix_" . $result->getMessage();
        $result->setMessage($message);
        return $result;
    }

    /*public function aroundGetMessageData($subject, $proceed)
    {
       return $proceed;
    }*/
}
