<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Controller\Transparent;

use Magento\TestFramework\TestCase\AbstractController;
use Zend\Stdlib\Parameters;

/**
 * Tests PayPal transparent redirect controller.
 */
class RedirectTest extends AbstractController
{
    /**
     * Tests transparent redirect for PayPal PayflowPro payment flow.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testRequestRedirect()
    {
        $redirectUri = 'paypal/transparent/redirect';
        $postData = [
            'BILLTOCITY' => 'culver city',
            'AMT' => '0.00',
            'BILLTOEMAIL' => 'user_1@example.com',
            'BILLTOSTREET' => '123 Freedom Blvd. #123 app.111',
            'VISACARDLEVEL' => '12',
            'SHIPTOCITY' => 'culver city'
        ];

        $this->setRequestUri($redirectUri);
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod('POST');

        $this->dispatch($redirectUri);

        $responseHtml = $this->getResponse()->getBody();
        try {
            $responseNvp = $this->convertToNvp($responseHtml);
            $this->assertEquals(
                $postData,
                $responseNvp,
                'POST form should contain all params from POST request'
            );
        } catch (\InvalidArgumentException $exception) {
            $this->fail($exception->getMessage());
        }

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

    /**
     * Converts HTML response to NVP structure
     *
     * @param string $response
     * @return array
     */
    private function convertToNvp(string $response): array
    {
        $document = new \DOMDocument();

        libxml_use_internal_errors(true);
        if (!$document->loadHTML($response)) {
            throw new \InvalidArgumentException(
                __('The response format was incorrect. Should be valid HTML')
            );
        }
        libxml_use_internal_errors(false);

        $document->getElementsByTagName('input');

        $convertedResponse = [];
        /** @var \DOMNode $inputNode */
        foreach ($document->getElementsByTagName('input') as $inputNode) {
            if (!$inputNode->attributes->getNamedItem('value')
                || !$inputNode->attributes->getNamedItem('name')
            ) {
                continue;
            }
            $convertedResponse[$inputNode->attributes->getNamedItem('name')->nodeValue]
                = $inputNode->attributes->getNamedItem('value')->nodeValue;
        }

        unset($convertedResponse['form_key']);

        return $convertedResponse;
    }
}
