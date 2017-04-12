<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;

/**
 * Join processor allows to join extension attributes during collections loading.
 */
interface JoinProcessorInterface
{
    /**
     * Processes extension attributes join instructions to add necessary joins to the collection of extensible entities.
     *
     * @param DbCollection $collection
     * @param string|null $extensibleEntityClass
     * @return void
     * @throws \LogicException
     */
    public function process(DbCollection $collection, $extensibleEntityClass = null);

    /**
     * Extract extension attributes into separate extension object.
     *
     * Complex extension attributes will be populated using flat data loaded.
     * Data items used for extension object population are unset from the $data.
     *
     * @param string $extensibleEntityClass
     * @param array $data
     * @return array
     * @throws \LogicException
     */
    public function extractExtensionAttributes($extensibleEntityClass, array $data);
}
