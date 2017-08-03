<?php
/**
 * Grid row url generator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * @api
 * @since 2.0.0
 */
class UrlGeneratorId implements \Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface
{
    /**
     * Create url for passed item using passed url model
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @since 2.0.0
     */
    public function getUrl($item)
    {
        return $item->getId();
    }
}
