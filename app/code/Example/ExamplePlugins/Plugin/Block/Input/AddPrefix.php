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
     * @return array
     */
    public function beforeGetMessageData($result): array
    {
        $message = "prefix_" . $result->getMessage();
        $result->setMessage($message);
        return [];
    }

    /**
     * Wrap string into <h1> tags </h1>
     *
     * @param $subject
     * @param $proceed
     * @return string
     */
    public function aroundGetMessageData($subject, $proceed): string
    {
        $result = $proceed();
        if ($result) {
            $result = "<h1>" . $result . "</h1>";
        }
        return $result;
    }
}
