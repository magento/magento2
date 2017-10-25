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
            return "<?php\nreturn array (\n" . $this->formatData($data, $comments) . "\n);\n";
        }
        return "<?php\nreturn " . var_export($data, true) . ";\n";
    }

    /**
     * Format supplied data
     *
     * @param string[] $data
     * @param string[] $comments
     * @param string $prefix
     * @return string
     */
    private function formatData($data, $comments = [], $prefix = '  ')
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

                $elements[] = $prefix . var_export($key, true) . ' => ' .
                    (!is_array($value) ? var_export($value, true) . ',' : '');

                if (is_array($value)) {
                    $elements[] = $prefix . 'array (';
                    $elements[] = $this->formatData($value, [], '  ' . $prefix);
                    $elements[] = $prefix . '),';
                }
            }
            return implode("\n", $elements);
        }

        return var_export($data, true);
    }
}
