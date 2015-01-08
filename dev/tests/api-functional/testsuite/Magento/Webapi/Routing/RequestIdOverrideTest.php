<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Webapi\Routing;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Class to test overriding request body identifier property with id passed in url path parameter
 *
 * Refer to \Magento\Webapi\Controller\Rest\Request::overrideRequestBodyIdWithPathParam
 */
class RequestIdOverrideTest extends \Magento\Webapi\Routing\BaseService
{
    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_restResourcePath;

    /**
     * @var \Magento\TestModule5\Service\V1\Entity\AllSoapAndRestBuilder
     */
    protected $itemBuilder;

    /**
     * @var string
     */
    protected $_soapService = 'testModule5AllSoapAndRest';

    protected function setUp()
    {
        $this->_markTestAsRestOnly('Request Id overriding is a REST based feature.');
        $this->_version = 'V1';
        $this->_restResourcePath = "/{$this->_version}/TestModule5/";
        $this->itemBuilder = Bootstrap::getObjectManager()
            ->create('Magento\TestModule5\Service\V1\Entity\AllSoapAndRestBuilder');
    }

    public function testOverride()
    {
        $itemId = 1;
        $incorrectItemId = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
        ];
        $item = $this->itemBuilder
            ->setEntityId($incorrectItemId)
            ->setName('test')
            ->create();
        $requestData = ['entityItem' => $item->__toArray()];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $itemId,
            $item[\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest::ID],
            'Identifier overriding failed.'
        );
    }

    public function testOverrideNested()
    {
        $firstItemId = 1;
        $secondItemId = 11;
        $incorrectItemId = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $firstItemId . '/nestedResource/' . $secondItemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
        ];
        $item = $this->itemBuilder
            ->setEntityId($incorrectItemId)
            ->setName('test')
            ->create();
        $requestData = ['entityItem' => $item->__toArray()];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $secondItemId,
            $item[\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest::ID],
            'Identifier overriding failed for nested resource request.'
        );
    }

    public function testOverrideAdd()
    {
        $itemId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
        ];
        $item = $this->itemBuilder
            ->setName('test')
            ->create();
        $requestData = ['entityItem' => $item->__toArray()];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $itemId,
            $item[\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest::ID],
            'Identifier replacing failed.'
        );
    }

    /**
     * Test if the framework works if camelCase path parameters are provided instead of valid snake case ones.
     * Webapi Framework currently accepts both cases due to shortcoming in Serialization (MAGETWO-29833).
     * Unless it is fixed this use case is valid.
     */
    public function testAddCaseMismatch()
    {
        $itemId = 1;
        $incorrectItemId = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath . $itemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = ['entityItem' => ['entityId' => $incorrectItemId, 'name' => 'test']];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $itemId,
            $item[\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest::ID],
            'Identifier overriding failed.'
        );
    }

    public function testOverrideWithScalarValues()
    {
        $firstItemId = 1;
        $secondItemId = 11;
        $incorrectItemId = 2;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/{$this->_version}/TestModule5/OverrideService/" . $firstItemId
                    . '/nestedResource/' . $secondItemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
        ];

        $requestData = ['entity_id' => $incorrectItemId, 'name' => 'test', 'orders' => true];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $secondItemId,
            $item[\Magento\TestModule5\Service\V1\Entity\AllSoapAndRest::ID],
            'Identifier overriding failed.'
        );
    }
}
