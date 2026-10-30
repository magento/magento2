<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Tax\Model\TaxClass\Type;

/**
 * Interface \Magento\Tax\Model\TaxClass\Type\TypeInterface
 *
 * @api
 */
interface TypeInterface
{
    /**
     * Check are any objects assigned to the tax class
     *
     * @return bool
     */
    public function isAssignedToObjects();

    /**
     * Get Collection of Tax Rules that are assigned to this tax class
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getAssignedToRules();

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return string
     */
    public function getObjectTypeName();
}
