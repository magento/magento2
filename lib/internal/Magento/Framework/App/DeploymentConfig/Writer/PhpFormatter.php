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
     * 4 space indentation for array formatting
     */
    const INDENT = '    ';

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
    private function formatData($data, $comments = [], $prefix = '    ')
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
                    $elements[] = $prefix . $this->varExportShort($key) . ' => [';
                    $elements[] = $this->formatData($value, [], '    ' . $prefix);
                    $elements[] = $prefix . '],';
                } else {
                    $elements[] = $prefix . $this->varExportShort($key) . ' => ' . $this->varExportShort($value) . ',';
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
     * @param mixed $var
     * @param int $depth
     * @return string
     */
    private function varExportShort($var, int $depth = 0): string
    {
        if (!is_array($var)) {
            return var_export($var, true);
        }

        $indexed = array_keys($var) === range(0, count($var) - 1);
        $expanded = [];
        foreach ($var as $key => $value) {
            $expanded[] = str_repeat(self::INDENT, $depth)
                . ($indexed ? '' : $this->varExportShort($key) . ' => ')
                . $this->varExportShort($value, $depth + 1);
        }

        return sprintf("[\n%s\n%s]", implode(",\n", $expanded), str_repeat(self::INDENT, $depth - 1));
    }
}
