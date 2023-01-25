<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Api;

/**
 * Interface to update attribute option
 *
 * @api
 */
interface AttributeOptionUpdateInterface
{
    /**
     * Update attribute option
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param int $optionId
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function update(
        string $entityType,
        string $attributeCode,
        int $optionId,
        \Magento\Eav\Api\Data\AttributeOptionInterface $option
    ): bool;
}
