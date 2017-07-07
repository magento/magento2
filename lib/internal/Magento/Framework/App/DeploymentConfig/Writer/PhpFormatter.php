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
            $elements = [];
            foreach ($data as $key => $value) {
                $comment = '  ';
                if (!empty($comments[$key])) {
                    $section = " * For the section: " . $key . "\n";
                    $exportedComment = is_string($comments[$key])
                        ? $comments[$key]
                        : var_export($comments[$key], true);
                    $comment = "  /**\n" . $section . " * " . str_replace("\n", "\n * ", $exportedComment) . "\n */\n";
                }
                $space = is_array($value) ? " \n" : ' ';
                $elements[] = $comment . var_export($key, true) . ' =>' . $space . var_export($value, true);
            }
            return "<?php\nreturn array (\n" . implode(",\n", str_replace("\n", "\n  ", $elements)) . "\n);\n";
        }
        return "<?php\nreturn " . var_export($data, true) . ";\n";
    }
}
