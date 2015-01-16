<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Files;

interface TestDataInterface
{
    public function getId();

    public function getAddress();

    public function isDefaultShipping();

    public function isRequiredBilling();
}
