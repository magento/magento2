<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Factories create different types of DTO`s from configuration
 * They need in order to provide validation
 */
interface FactoryInterface
{
    /**
     * Compute and return effective value of an argument
     *
     * @param  array $data
     * @return ElementInterface
     */
    public function create(array $data);
}
