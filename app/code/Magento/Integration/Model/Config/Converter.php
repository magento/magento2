<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Config;

/**
 * Converter of integration.xml content into array format.
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
        $result = array();
        /** @var \DOMNodeList $integrations */
        $integrations = $source->getElementsByTagName('integration');
        /** @var \DOMElement $integration */
        foreach ($integrations as $integration) {
            if ($integration->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $integrationName = $integration->attributes->getNamedItem('name')->nodeValue;
            $result[$integrationName] = array();

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
