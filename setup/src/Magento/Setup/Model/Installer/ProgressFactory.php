<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Installer;

use Magento\Setup\Model\Installer;
use Magento\Setup\Model\WebLogger;

/**
 * Factory for progress indicator model
 * @since 2.0.0
 */
class ProgressFactory
{
    /**
     * Creates a progress indicator from log contents
     *
     * @param WebLogger $logger
     * @return Progress
     * @since 2.0.0
     */
    public function createFromLog(WebLogger $logger)
    {
        $total = 1;
        $current = 0;
        $contents = implode('', $logger->get());
        if (preg_match_all(Installer::PROGRESS_LOG_REGEX, $contents, $matches, PREG_SET_ORDER)) {
            $last = array_pop($matches);
            list(, $current, $total) = $last;
        }
        $progress = new Progress($total, $current);
        return $progress;
    }
}
