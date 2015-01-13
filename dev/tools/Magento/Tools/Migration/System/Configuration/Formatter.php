<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
