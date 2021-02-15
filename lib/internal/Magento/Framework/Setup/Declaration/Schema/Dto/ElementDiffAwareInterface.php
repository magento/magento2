<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Element diff provider interface.
 *
 * This interface provides all params, that should participate in elements comparison.
 *
 * @api
 */
interface ElementDiffAwareInterface
{
    /**
     * Return sensitive params, with respect of which we will compare db and xml
     * For instance,
     *  padding => '2'
     *  identity => null
     *
     * Such params as name, renamedTo, disabled, tableName should be avoided here.
     * As this params are system and must not participate in comparison at all.
     *
     * @return array
     */
    public function getDiffSensitiveParams();
}
