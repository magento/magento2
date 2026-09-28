<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Entity attribute select source interface
 *
 * Source is providing the selection options for user interface
 *
 * @api
 * @since 100.0.2
 */
interface SourceInterface
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions();

    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value);
}
