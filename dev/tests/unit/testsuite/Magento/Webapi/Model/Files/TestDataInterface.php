<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Webapi\Model\Files;

interface TestDataInterface
{
    public function getId();

    public function getAddress();

    public function isDefaultShipping();

    public function isRequiredBilling();
}
