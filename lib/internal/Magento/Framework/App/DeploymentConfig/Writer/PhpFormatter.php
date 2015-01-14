<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig\Writer;

/**
 * A formatter for deployment configuration that presents it as a PHP-file that returns data
 */
class PhpFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($data)
    {
        return "<?php\nreturn " . var_export($data, true) . ";\n";
    }
}
