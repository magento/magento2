<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Request;

/**
 * Test class for \Magento\Framework\App\Request\Http.
 *
 * @magentoAppArea frontend
 */
class HttpTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test Http Request controller name for invalid requestUri route.
     *
     * @param string $requestUri
     * @dataProvider setPathInfoInvalidRouteDataProvider
     */
    public function testSetPathInfoInvalidRoute($requestUri)
    {
        $this->dispatch($requestUri);
        parent::assert404NotFound();
    }

    /**
     * Test Http Request controller name for valid requestUri route.
     *
     * @param string $requestUri
     * @dataProvider setPathInfoValidRouteDataProvider
     */
    public function testSetPathInfoValidRoute($requestUri)
    {
        $this->dispatch($requestUri);
        $this->assertNotEquals('noroute', $this->getRequest()->getControllerName());
        $this->assertNotContains('404 Not Found', $this->getResponse()->getBody());
    }

    /**
     * @return array
     */
    public function setPathInfoInvalidRouteDataProvider()
    {
        return [
            ['/index.phpmodule'],
            ['/index.phpmodule/contact'],
            ['//index.phpmodule/contact'],
        ];
    }

    /**
     * @return array
     */
    public function setPathInfoValidRouteDataProvider()
    {
        return [
            ['/'],
            ['//'],
        ];
    }
}
