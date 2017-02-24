<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

/**
 * Interface AttributeSetManagementInterface
 * @api
 */
interface AttributeSetManagementInterface
{
    /**
     * Create attribute set from data
     *
     * @param string $entityTypeCode
     * @param \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet
     * @param int $skeletonId
     * @return \Magento\Eav\Api\Data\AttributeSetInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create(
        $entityTypeCode,
        \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet,
        $skeletonId
    );
}
