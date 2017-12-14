<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Casters;

/**
 * Casters helps to add some default values, some default attributes by types
 * Also caster helps to make schema created with old Magento adapter and schema from XML file
 * the same
 */
interface CasterInterface
{
    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return array
     */
    public function cast(array $data);
}
