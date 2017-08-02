<?php
/**
 * Row Generator Interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * @api
 * @since 2.0.0
 */
interface GeneratorInterface
{
    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getUrl($item);
}
