<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Interface \Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface
 *
 * @api
 */
interface FormatterInterface
{
    /**
     * Format deployment configuration
     *
     * @param array $data
     * @param array $comments
     * @return string
     */
    public function format($data, array $comments = []);
}
