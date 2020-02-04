<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * International AVS response validator checks configuration option
 * and fails validation if PayPal returns IAVS's check marked as international issuing bank.
 */
class IAVSResponse implements ValidatorInterface
{
    /**
     * Indicates whether AVS response is international (Y),
     * US (N), or cannot be determined (X).
     * @var string
     */
    private static $iavs = 'iavs';

    /**
     * @var string
     */
    private static $negativeResponseCode = 'y';

    /**
     * @inheritdoc
     */
    public function validate(DataObject $response, Transparent $transparentModel)
    {
        $config = $transparentModel->getConfig();
        // the IAVS configuration setting is not enabled
        if (!$config->getValue('avs_international')) {
            return true;
        }

        if (strtolower((string) $response->getData(self::$iavs)) === self::$negativeResponseCode) {
            $response->setRespmsg('International AVS indicator does not match.');
            return false;
        }

        return true;
    }
}
