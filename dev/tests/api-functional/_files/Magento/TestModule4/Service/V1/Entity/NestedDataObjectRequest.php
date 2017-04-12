<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1\Entity;

class NestedDataObjectRequest extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectRequest
     */
    public function getDetails()
    {
        return $this->_get('details');
    }

    /**
     * @param \Magento\TestModule4\Service\V1\Entity\DataObjectRequest $details
     * @return $this
     */
    public function setDetails(\Magento\TestModule4\Service\V1\Entity\DataObjectRequest $details = null)
    {
        return $this->setData('details', $details);
    }
}
