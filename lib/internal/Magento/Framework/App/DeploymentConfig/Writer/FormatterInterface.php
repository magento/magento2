<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig\Writer;

interface FormatterInterface
{
    /**
     * Format deployment configuration
     *
     * @param array $data
     * @return string
     */
    public function format($data);
}
