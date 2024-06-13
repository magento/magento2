<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class Nested extends AbstractExtensibleObject
{
    /**
     * @return \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple
     */
    public function getDetails()
    {
        return $this->_get('details');
    }

    /**
     * @param \Magento\Webapi\Service\Entity\Simple $details
     * @return $this
     */
    public function setDetails($details)
    {
        return $this->setData('details', $details);
    }
}
