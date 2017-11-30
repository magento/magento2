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
    const INDENT = '  ';

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
            return "<?php\nreturn [\n" . $this->formatData($data, $comments) . "\n];\n";
        }
        return "<?php\nreturn " . $this->varExportShort($data, true) . ";\n";
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

                $elements[] = $prefix . $this->varExportShort($key) . ' => ' .
                    (!is_array($value) ? $this->varExportShort($value) . ',' : '');

                if (is_array($value)) {
                    $elements[] = $prefix . '[';
                    $elements[] = $this->formatData($value, [], '  ' . $prefix);
                    $elements[] = $prefix . '],';
                }
            }
            return implode("\n", $elements);
        }

        return var_export($data, true);
    }

    /**
     * If variable to export is an array, format with the php >= 5.4 short array syntax. Otherwise use
     * default var_export functionality.
     *
     * @param mixed   $var
     * @param integer $depth
     * @return string
     */
    private function varExportShort($var, $depth=0) {
        if (gettype($var) === 'array') {
            $indexed = array_keys($var) === range(0, count($var) - 1);
            $r = [];
            foreach ($var as $key => $value) {
                $r[] = str_repeat(self::INDENT, $depth)
                    . ($indexed ? '' : $this->varExportShort($key) . ' => ')
                    . $this->varExportShort($value, $depth + 1);
            }
            return sprintf("[\n%s\n%s]", implode(",\n", $r), str_repeat(self::INDENT, $depth - 1));
        }

        return var_export($var, TRUE);
    }
}
