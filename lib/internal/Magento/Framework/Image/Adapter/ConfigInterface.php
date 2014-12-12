<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Image\Adapter;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getAdapterAlias();

    /**
     * @return array
     */
    public function getAdapters();
}
