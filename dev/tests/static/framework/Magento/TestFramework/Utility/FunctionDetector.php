<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        if (!$regexp) {
            return $result;
        }

        $fileContent = \Magento\TestFramework\Utility\ChangedFiles::getChangedContent($filePath);
        $file = file($filePath);

        return $fileContent
            ? $this->grepChangedContent($file, $regexp, $functions, $fileContent)
            : $this->grepFile($file, $regexp);
    }

    /**
     * Grep only changed content.
     *
     * @param array $file
     * @param string $regexp
     * @param string[] $functions
     * @param string $fileContent
     * @return array
     */
    public function grepChangedContent(array $file, $regexp, $functions, $fileContent)
    {
        $result = [];
        $matches = preg_grep($regexp, explode("\n", $fileContent));
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

        return $result;
    }

    /**
     * Grep File.
     *
     * @param array $file
     * @param string $regexp
     * @return array
     */
    public function grepFile(array $file, $regexp)
    {
        $result = [];
        array_unshift($file, '');
        $lines = preg_grep($regexp, $file);
        foreach ($lines as $lineNumber => $line) {
            if (preg_match_all($regexp, $line, $matches)) {
                $result[$lineNumber] = $matches[1];
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
