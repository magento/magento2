<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig\Writer;

/**
 * A formatter for deployment configuration that presents it as a PHP-file that returns data
 */
class PhpFormatter implements FormatterInterface
{
    /**
     * Format deployment configuration.
     * If $comments is present, each item will be added
     * as comment to the corresponding section
     *
     * {@inheritdoc}
     */
    public function format($data, array $comments = [])
    {
        if (!empty($comments) && is_array($data)) {
            return "<?php\nreturn array (\n" . $this->formatData($data, $comments, '  ') . "\n);\n";
        }
        return "<?php\nreturn " . var_export($data, true) . ";\n";
    }

    /**
     * Format supplied data
     *
     * @param $data
     * @param $comments
     * @param string $prefix
     * @return string
     */
    protected function formatData($data, $comments, $prefix = '')
    {
        $elements = [];

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!empty($comments[$key])) {
                    $elements[] = $prefix . '/**';
                    $elements[] = $prefix . ' * For the section: ' . $key;

                    foreach (explode("\n", $comments[$key]) as $commentLine) {
                        $elements[] = $prefix . ' * ' . $commentLine;
                    }

                    $elements[] = $prefix . " */";
                }

                if (is_array($value)) {
                    $elements[] = $prefix . var_export($key, true) . ' => ';
                    $elements[] = $prefix . 'array (';
                    $elements[] = $this->formatData($value, [], '  ' . $prefix);
                    $elements[] = $prefix . '),';
                } else {
                    $elements[] = $prefix . var_export($key, true) . ' => ' . var_export($value, true) . ',';
                }
            }
            return implode("\n", $elements);
        }

        return var_export($data, true);
    }
}
