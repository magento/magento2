<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Shell;

/**
 * A parser for complex parameters in command-line arguments
 *
 * Transforms parameter formatted as a URL query string into an array
 */
class ComplexParameter
{
    /**
     * Default regex pattern for searching the parameter
     */
    const DEFAULT_PATTERN = '/^\-\-%s=(.+)$/';

    /**
     * Argument name
     *
     * @var string
     */
    private $name;

    /**
     * Regex pattern for searching the parameter among arguments
     *
     * @var string
     */
    private $pcre;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $pattern
     */
    public function __construct($name, $pattern = self::DEFAULT_PATTERN)
    {
        $this->name = $name;
        $this->pcre = sprintf($pattern, preg_quote($name, '/'));
    }

    /**
     * Searches and parses the value from an array of arguments
     *
     * @param string[] $input
     * @return array
     */
    public function getFromArray($input)
    {
        foreach ($input as $row) {
            $result = $this->getFromString($row);
            if ($result) {
                return $result;
            }
        }
        return [];
    }

    /**
     * Parses the value from a specified argument string
     *
     * @param string $string
     * @return array
     */
    public function getFromString($string)
    {
        if (preg_match($this->pcre, $string, $matches)) {
            parse_str($matches[1], $result);
            return $result;
        }
        return [];
    }

    /**
     * Searches the value parameter in an "argv" array and merges it recursively into specified array
     *
     * @param array $server
     * @param array $into
     * @return array
     */
    public function mergeFromArgv($server, array $into = [])
    {
        $result = $into;
        if (isset($server['argv'])) {
            $value = $this->getFromArray($server['argv']);
            $result = array_replace_recursive($into, $value);
        }
        return $result;
    }
}
