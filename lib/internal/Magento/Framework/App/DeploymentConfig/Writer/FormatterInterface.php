<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Interface \Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface
 *
 * @since 2.0.0
 */
interface FormatterInterface
{
    /**
     * Format deployment configuration
     *
     * @param array $data
     * @param array $comments
     * @return string
     * @since 2.0.0
     */
    public function format($data, array $comments = []);
}
