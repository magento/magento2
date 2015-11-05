<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Swagger\Test\Page\SwaggerUiPage;

/**
 * Precondition:
 * 1. Magento is installed
 *
 * Steps:
 * 1. Open swagger ui page in browser
 * 2. Enter service name in endpoint input field
 * 3. Click "Explore" button
 * 4. Select service name to show operations
 * 5. Click operation name to show operation details
 * 6. Perform all assertions
 *
 * @group Swagger_(PS)
 * @ZephyrId MAGETWO-41381, MAGETWO-41383
 */
class SwaggerUiForRestApiTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Service name
     *
     * @var string
     */
    protected $serviceName;

    /**
     * Service endpoints
     *
     * @var array
     */
    protected $endpoints;

    /**
     * Swagger Page
     *
     * @var SwaggerUiPage
     */
    protected $swaggerPage;

    /**
     * Injection data
     *
     * @param SwaggerUiPage $swaggerPage
     * @return void
     */
    public function __inject(
        SwaggerUiPage $swaggerPage
    ) {
        $this->swaggerPage = $swaggerPage;
    }

    /**
     * Load Swagger UI for Rest API
     *
     * @param string $serviceName
     * @param array $endpoints
     * @return void
     */
    public function test(
        $serviceName,
        array $endpoints
    ) {
        $this->serviceName = $serviceName;
        $this->endpoints = $endpoints;
        $this->swaggerPage->open();
        $this->swaggerPage->expandServiceContent($this->serviceName);
        foreach ($endpoints as $endpoint) {
            $this->swaggerPage->expandEndpointContent($serviceName, $endpoint);
        }
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->endpoints as $endpoint) {
            $this->swaggerPage->closeEndpointContent($this->serviceName, $endpoint);
        }
        $this->swaggerPage->closeServiceContent($this->serviceName);
        $this->swaggerPage->close();
    }
}
