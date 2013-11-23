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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Config;

/**
 * Converter of integration.xml content into array format.
 */
class Converter implements \Magento\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_NAME = 'name';
    const KEY_EMAIL = 'email';
    const KEY_AUTHENTICATION = 'authentication';
    const KEY_AUTHENTICATION_TYPE = 'type';
    const KEY_AUTHENTICATION_ENDPOINT_URL = 'endpoint_url';
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
            $integrationId = $integration->attributes->getNamedItem('id')->nodeValue;
            $result[$integrationId] = array();

            /** @var \DOMElement $name */
            $name = $integration->getElementsByTagName('name')->item(0)->nodeValue;
            $result[$integrationId][self::KEY_NAME] = $name;

            /** @var \DOMElement $email */
            $email = $integration->getElementsByTagName('email')->item(0)->nodeValue;
            $result[$integrationId][self::KEY_EMAIL] = $email;

            /** @var \DOMNodeList $authentication */
            $authentication = $integration->getElementsByTagName('authentication')->item(0);

            $authenticationType = $authentication->attributes->getNamedItem('type')->nodeValue;
            $result[$integrationId][self::KEY_AUTHENTICATION] = array(
                self::KEY_AUTHENTICATION_TYPE => $authenticationType
            );

            /** @var \DOMElement $endpointUrl */
            $endpointUrl = $integration->getElementsByTagName('endpoint_url')->item(0);
            if ($endpointUrl) {
                $result[$integrationId][self::KEY_AUTHENTICATION][self::KEY_AUTHENTICATION_ENDPOINT_URL] =
                    $endpointUrl->nodeValue;
            }
        }
        return $result;
    }
}
