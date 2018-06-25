<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Files;

class TestDataObject implements TestDataInterface
{
    /**
     * @return string
     */
    public function getId()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return 'someAddress';
    }

    /**
     * @return string
     */
    public function isDefaultShipping()
    {
        return 'true';
    }

    /**
     * @return string
     */
    public function isRequiredBilling()
    {
        return 'false';
    }
}
