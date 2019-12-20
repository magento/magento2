<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExamplePlugins\Plugin\Block\Input;

use Example\ExampleFrontendUi\Block\Input\Index;

/**
 * Class modifies message
 */
class AddSuffixToOutputPlugin
{
    /**
     * Add "_suffix"
     *
     * @param Index $result
     * @return string
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function afterGetMessageData($result): string
    {
        return  $result->getMessage() . "_suffix";
    }
}
