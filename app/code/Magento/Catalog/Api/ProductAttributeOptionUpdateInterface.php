<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

/**
 * Interface to update product attribute option
 *
 * @api
 */
interface ProductAttributeOptionUpdateInterface
{
    /**
     * Update attribute option
     *
     * @param string $attributeCode
     * @param int $optionId
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function update(
        string $attributeCode,
        int $optionId,
        \Magento\Eav\Api\Data\AttributeOptionInterface $option
    ): bool;
}
