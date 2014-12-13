<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
