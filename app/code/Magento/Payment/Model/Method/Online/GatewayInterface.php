<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Online;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\ConfigInterface;

/**
 * Gateway interface for online payment methods
 */
interface GatewayInterface
{
    /**
     * Post request to gateway and return response
     *
     * @param Object $request
     * @param ConfigInterface $config
     *
     * @return DataObject
     *
     * @throws \Exception
     */
    public function postRequest(DataObject $request, ConfigInterface $config);
}
