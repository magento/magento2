<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Quote\Api\Data\AddressAdditionalDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class \Magento\Quote\Model\AddressAdditionalData
 *
 * @since 2.0.0
 */
class AddressAdditionalData extends AbstractExtensibleModel implements AddressAdditionalDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\AddressAdditionalDataExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\AddressAdditionalDataExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
