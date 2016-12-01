<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

/**
 * Check if one or more functions are used in the file
 */
class FunctionDetector
{
    /**
     * Detect functions in given file
     *
     * return result in this format:
     *  [
     *      line_number => [
     *          function_name_1,
     *          function_name_2,
     *          function_name_3,
     *      ],
     *      line_number => [
     *          function_name_1,
     *          function_name_2,
     *          function_name_3,
     *      ],
     *  ]
     *
     * @param string $filePath
     * @param string[] $functions
     * @return array
     */
    public function detect($filePath, $functions)
    {
        $result = [];
        $regexp = $this->composeRegexp($functions);

        if ($regexp) {
            $fileContent = \Magento\TestFramework\Utility\ChangedFiles::getChangedContent($filePath);
            $matches = preg_grep($regexp, explode("\n", $fileContent));
            $file = file($filePath);
            if (!empty($matches)) {
                foreach ($matches as $line) {
                    $actualFunctions = [];
                    foreach ($functions as $function) {
                        if (false !== strpos($line, $function)) {
                            $actualFunctions[] = $function;
                        }
                    }
                    $result[array_search($line . "\n", $file) + 1] = $actualFunctions;
                }
            }
        }
        return $result;
    }

    /**
     * Compose regular expression
     *
     * @param array $functions
     * @return string
     */
    private function composeRegexp(array $functions)
    {
        if (empty($functions)) {
            return '';
        }
        return '/(?<!function |->|::)\b(' . join('|', $functions) . ')\s*\(/i';
    }
}
