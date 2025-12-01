<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * DTO Element Factory Interface.
 *
 * @api
 */
interface FactoryInterface
{
    /**
     * Create element using definition data array.
     *
     * @param  array $data
     * @return ElementInterface
     */
    public function create(array $data);
}
