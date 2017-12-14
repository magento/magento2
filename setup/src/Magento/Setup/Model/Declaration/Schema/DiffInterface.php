<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Used to compare schema DTO`s and based on comparison
 * add needed operations, like create, update, etc
 */
interface DiffInterface
{
    /**
     * Compare declared and generated nodes
     *
     * @param ElementInterface $declaredNode
     * @param ElementInterface $generatedNode
     * @param ChangeRegistry $changeRegistry
     * @return void
     */
    public function diff(
        ElementInterface $declaredNode,
        ElementInterface $generatedNode,
        ChangeRegistry $changeRegistry
    );
}
