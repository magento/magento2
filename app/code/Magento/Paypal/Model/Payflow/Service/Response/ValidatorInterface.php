<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * Validate data
     *
     * @param Object $response
     * @return bool
     */
    public function validate(DataObject $response);
}
