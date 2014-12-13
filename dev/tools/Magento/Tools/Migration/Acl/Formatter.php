<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\Acl;

class Formatter
{
    /**
     * @param string $string
     * @param string $paramenters
     * @return string
     */
    public function parseString($string, $paramenters)
    {
        $tidy = tidy_parse_string($string, $paramenters);
        return $tidy->value;
    }
}
