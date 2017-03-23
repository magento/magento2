<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Files;

class TestDataObject implements TestDataInterface
{
    public function getId()
    {
        return '1';
    }

    public function getAddress()
    {
        return 'someAddress';
    }

    public function isDefaultShipping()
    {
        return 'true';
    }

    public function isRequiredBilling()
    {
        return 'false';
    }
}
