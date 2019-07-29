<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AuthorizenetAcceptjs\Plugin;

use Magento\Framework\View\Asset\Minification;

/**
 * Plugin for Magento\Framework\View\Asset\Minification.
 */
class ExcludeFilesFromMinification
{
    /**
     * Add Accept.js to exclude from minification
     *
     * @param Minification $subject
     * @param array $result
     * @param $contentType
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType)
    {
        if ($contentType == 'js') {
            $result[] = '/v1/Accept';
        }
        return $result;
    }
}
