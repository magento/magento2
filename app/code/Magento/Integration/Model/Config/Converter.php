<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Config;

/**
 * Converter of integration.xml content into array format.
 *
 * @deprecated
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_EMAIL = 'email';

    const KEY_AUTHENTICATION_ENDPOINT_URL = 'endpoint_url';

    const KEY_IDENTITY_LINKING_URL = 'identity_link_url';

    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNodeList $integrations */
        $integrations = $source->getElementsByTagName('integration');
        /** @var \DOMElement $integration */
        foreach ($integrations as $integration) {
            if ($integration->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $integrationName = $integration->attributes->getNamedItem('name')->nodeValue;
            $result[$integrationName] = [];

            /** @var \DOMElement $email */
            $email = $integration->getElementsByTagName('email')->item(0)->nodeValue;
            $result[$integrationName][self::KEY_EMAIL] = $email;
            if ($integration->getElementsByTagName('endpoint_url')->length) {
                /** @var \DOMElement $endpointUrl */
                $endpointUrl = $integration->getElementsByTagName('endpoint_url')->item(0)->nodeValue;
                $result[$integrationName][self::KEY_AUTHENTICATION_ENDPOINT_URL] = $endpointUrl;
            }
            if ($integration->getElementsByTagName('identity_link_url')->length) {
                /** @var \DOMElement $identityLinkUrl */
                $identityLinkUrl = $integration->getElementsByTagName('identity_link_url')->item(0)->nodeValue;
                $result[$integrationName][self::KEY_IDENTITY_LINKING_URL] = $identityLinkUrl;
            }
        }
        return $result;
    }
}
