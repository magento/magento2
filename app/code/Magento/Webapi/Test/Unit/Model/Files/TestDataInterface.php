<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Model\Files;

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
}
