<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
