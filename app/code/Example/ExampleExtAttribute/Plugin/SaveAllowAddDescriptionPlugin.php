<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExampleExtAttribute\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class SaveAllowAddDescriptionPlugin
{
    public function afterSave(CustomerRepositoryInterface $subject, CustomerInterface $resultCustomer)
    {
        $extensionAttributes = $resultCustomer->getExtensionAttributes();


        return $resultCustomer;
    }

}
