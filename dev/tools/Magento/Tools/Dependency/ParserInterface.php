<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency;

/**
 * Parser Interface
 */
interface ParserInterface
{
    /**
     * Parse files
     *
     * @param array $options
     * @return array
     */
    public function parse(array $options);
}
