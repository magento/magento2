<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\System\Configuration;

class Formatter
{
    /**
     * @param string $string
     * @param string $parameters
     * @return string
     */
    public function parseString($string, $parameters)
    {
        $tidy = tidy_parse_string($string, $parameters);
        return $tidy->value;
    }
}
