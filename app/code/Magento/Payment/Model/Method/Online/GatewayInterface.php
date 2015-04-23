<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Online;

use Magento\Framework\Object;
use Magento\Payment\Model\Method\ConfigInterface;

/**
 * Gateway interface for online payment methods
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
interface GatewayInterface
{
    /**
     * Post request to gateway and return response
     *
     * @param Object $request
     * @param ConfigInterface $config
     *
     * @return Object
     *
     * @throws \Exception
     */
    public function postRequest(Object $request, ConfigInterface $config);
}
