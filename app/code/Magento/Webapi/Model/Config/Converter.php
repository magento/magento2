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
namespace Magento\Webapi\Model\Config;

/**
 * Converter of webapi.xml content into array format.
 */
class Converter implements \Magento\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_SERVICE_CLASS = 'class';

    const KEY_BASE_URL = 'baseUrl';

    const KEY_SERVICE_METHOD = 'method';

    const KEY_IS_SECURE = 'isSecure';

    const KEY_HTTP_METHOD = 'httpMethod';

    const KEY_SERVICE_METHODS = 'methods';

    const KEY_METHOD_ROUTE = 'route';

    const KEY_ACL_RESOURCES = 'resources';

    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = array();
        /** @var \DOMNodeList $services */
        $services = $source->getElementsByTagName('service');
        /** @var \DOMElement $service */
        foreach ($services as $service) {
            if ($service->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $serviceClass = $service->attributes->getNamedItem('class')->nodeValue;
            $result[$serviceClass] = array(
                self::KEY_SERVICE_CLASS => $serviceClass,
                self::KEY_SERVICE_METHODS => array()
            );

            /** @var \DOMAttr $baseUrlNode */
            $baseUrlNode = $service->attributes->getNamedItem('baseUrl');
            if ($baseUrlNode) {
                $result[$serviceClass][self::KEY_BASE_URL] = $baseUrlNode->nodeValue;
            }

            /** @var \DOMNodeList $restRoutes */
            $restRoutes = $service->getElementsByTagName('rest-route');
            /** @var \DOMElement $restRoute */
            foreach ($restRoutes as $restRoute) {
                if ($restRoute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $httpMethod = $restRoute->attributes->getNamedItem('httpMethod')->nodeValue;
                $method = $restRoute->attributes->getNamedItem('method')->nodeValue;

                $resources = $restRoute->attributes->getNamedItem('resources')->nodeValue;
                /** Allow whitespace usage after comma. */
                $resources = str_replace(', ', ',', $resources);
                $resources = explode(',', $resources);

                $isSecureAttribute = $restRoute->attributes->getNamedItem('isSecure');
                $isSecure = $isSecureAttribute ? true : false;
                $path = (string)$restRoute->nodeValue;

                $result[$serviceClass][self::KEY_SERVICE_METHODS][$method] = array(
                    self::KEY_HTTP_METHOD => $httpMethod,
                    self::KEY_SERVICE_METHOD => $method,
                    self::KEY_METHOD_ROUTE => $path,
                    self::KEY_IS_SECURE => $isSecure,
                    self::KEY_ACL_RESOURCES => $resources
                );
            }
        }
        return $result;
    }
}
