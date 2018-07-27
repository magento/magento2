<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * Validate data
     *
     * @param DataObject $response
     * @param Transparent|null $transparentModel
     * @return bool
     */
    public function validate(DataObject $response, Transparent $transparentModel);
}
