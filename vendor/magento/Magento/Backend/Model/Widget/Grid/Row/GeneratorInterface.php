<?php
/**
 * Row Generator Interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

interface GeneratorInterface
{
    /**
     * @param \Magento\Framework\Object $item
     * @return string
     */
    public function getUrl($item);
}
