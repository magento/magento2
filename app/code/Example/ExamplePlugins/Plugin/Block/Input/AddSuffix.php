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
class AddSuffix
{
    /**
     * Add "_suffix"
     *
     * @param $result
     * @return string
     */
    public function afterGetMessageData($result): string
    {
        return  $result->getMessage() . "_suffix";
    }
}
