<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Config;

interface DataInterface
{
    /**
     * @param string|null $path
     * @return string|array
     */
    public function getValue($path);
}
