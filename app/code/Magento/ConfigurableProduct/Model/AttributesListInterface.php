<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
