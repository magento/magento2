<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\Webapi;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Webapi;
use Magento\Mtf\Util\Protocol\SoapTransport;

/**
 * Class CreateCustomer
 *
 */
class CreateCustomer extends Webapi
{
    /**
     * Create customer through request
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $configuration = $this->_configuration->getParameter('handler/webapi');

        $soap = new SoapTransport($configuration['soap']);
        return $soap->call('customerCustomerList', $fixture->getData());
    }
}
