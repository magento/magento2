<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

/**
 * @api
 * @since 100.0.2
 */
interface AttributesListInterface
{
    /**
     * Retrieve list of attributes
     *
     * @param array $ids
     * @return array
     */
    public function getAttributes($ids);
}
