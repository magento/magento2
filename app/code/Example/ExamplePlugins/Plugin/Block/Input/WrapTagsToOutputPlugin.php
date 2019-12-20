<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExamplePlugins\Plugin\Block\Input;

use Example\ExampleFrontendUi\Block\Input\Index;

/**
 * Wrap tags with output
 */
class WrapTagsToOutputPlugin
{
    /**
     * Wrap string into <h1> tags </h1>
     *
     * @param Index $subject
     * @param \Closure $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
