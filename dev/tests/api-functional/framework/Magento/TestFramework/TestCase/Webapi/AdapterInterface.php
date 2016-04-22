<?php
/**
 * API tests adapter interface.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase\Webapi;

interface AdapterInterface
{
    /**
     * Perform call to the specified service method.
     *
     * @param array $serviceInfo <pre>
     * array(
     *     'rest' => array(
     *         'resourcePath' => $resourcePath,                 // e.g. /products/:id
     *         'httpMethod' => $httpMethod,                     // e.g. GET
     *         'token' => '21hasbtlaqy8t3mj73kjh71cxxkqj4aq'    // optional : for token based Authentication. Will
     *                                                             override default Oauth based authentication provided
     *                                                             by test framework
     *     ),
     *     'soap' => array(
     *         'service' => $soapService,                       // soap service name with Version suffix e.g.
     *                                                             catalogProductV1, customerV2
     *         'operation' => $operation                        // soap operation name e.g. catalogProductCreate
     *         'token' => '21hasbtlaqy8t3mj73kjh71cxxkqj4aq'    // optional : for token based Authentication. Will
     *                                                             override default Oauth based authentication provided
     *                                                             by test framework
     *     ),
     *     OR
     *     'serviceInterface' => $phpServiceInterfaceName,      // e.g. \Magento\Catalog\Api\ProductInterface
     *     'method' => $serviceMethodName                       // e.g. create
     *     'entityId' => $entityId                              // is used in REST route placeholder (if applicable)
     * );
     * </pre>
     * @param array $arguments
     * @param string|null $storeCode if store code not provided, default store code will be used
     * @param \Magento\Integration\Model\Integration|null $integration
     * @return array|string|int|float|bool
     */
    public function call($serviceInfo, $arguments = [], $storeCode = null, $integration = null);
}
