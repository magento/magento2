<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
