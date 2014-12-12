<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Structure;

interface SearchInterface
{
    /**
     * Find element by path
     *
     * @param string $path
     * @return ElementInterface|null
     */
    public function getElement($path);
}
