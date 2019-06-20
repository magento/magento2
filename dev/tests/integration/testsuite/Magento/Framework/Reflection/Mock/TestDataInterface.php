<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Mock;

interface TestDataInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function isDefaultShipping();

    /**
     * @return string
     */
    public function isRequiredBilling();

    /**
     * @return \Magento\Framework\Api\ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes();
}
