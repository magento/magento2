<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Files;

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
