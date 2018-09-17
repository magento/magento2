<?php
/**
 * Row Generator Interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

interface GeneratorInterface
{
    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @api
     */
    public function getUrl($item);
}
