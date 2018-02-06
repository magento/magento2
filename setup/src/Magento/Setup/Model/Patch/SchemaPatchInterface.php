<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Patch;

use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * This interface describe script, that atomic operations with db schema (DDL) in SQL database
 */
interface SchemaPatchInterface extends PatchDisableInterface
{
    /**
     * Provide system ugrade or install
     *
     * @return void
     */
    public function apply(SchemaSetupInterface $moduleDataSetup);

    /**
     * Provide system downgrade
     *
     * @return void
     */
    public function revert(SchemaSetupInterface $moduleDataSetup);
}
