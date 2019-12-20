<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExamplePlugins\Plugin\Block\Input;

use Example\ExampleFrontendUi\Block\Input\Index;

/**
 * Class modifies message.
 */
class AddPrefixToOutputPlugin
{
    /**
     * Add "prefix_".
     *
     * @param Index $result
     * @return array
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function beforeGetMessageData($result): array
    {
        $message = "prefix_" . $result->getMessage();
        $result->setMessage($message);
        return [];
    }
}
