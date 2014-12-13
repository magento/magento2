<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
