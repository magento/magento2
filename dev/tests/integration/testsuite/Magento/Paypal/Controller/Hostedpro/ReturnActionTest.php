<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Hostedpro;

use Magento\TestFramework\TestCase\AbstractController;
use Laminas\Stdlib\Parameters;

/**
 * Tests PayPal HostedPro return controller.
 */
class ReturnActionTest extends AbstractController
{
    /**
     * Tests customer redirect on success page after return from PayPal HostedPro payment.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testReturnRedirect()
    {
        $redirectUri = 'paypal/hostedpro/return';
        $this->setRequestUri($redirectUri);
        $this->getRequest()->setMethod('POST');

        $this->dispatch($redirectUri);
        $this->assertRedirect($this->stringContains('checkout/onepage/success'));

        $this->assertEmpty(
            $_SESSION,
            'Session start has to be skipped for current controller'
        );
    }

    /**
     * Sets REQUEST_URI into request object.
     *
     * @param string $requestUri
     * @return void
     */
    private function setRequestUri(string $requestUri)
    {
        $request = $this->getRequest();
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('requestUri');
        $property->setAccessible(true);
        $property->setValue($request, null);

        $request->setServer(new Parameters(['REQUEST_URI' => $requestUri]));
    }
}
