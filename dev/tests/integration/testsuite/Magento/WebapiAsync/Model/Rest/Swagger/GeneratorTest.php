<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiAsync\Model\Rest\Swagger;

use Magento\Framework\Webapi\Authorization;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessorMock;
use Magento\WebapiAsync\Model\AuthorizationMock;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ServiceMetadata
     */
    private $serviceMetadata;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->configure([
            'preferences' => [
                Authorization::class => AuthorizationMock::class,
                AsynchronousSchemaRequestProcessor::class => AsynchronousSchemaRequestProcessorMock::class
            ]
        ]);

        $this->generator = $objectManager->create(Generator::class);
        $this->serviceMetadata = $objectManager->create(ServiceMetadata::class);
    }

    public function testGenerateAsync()
    {
        $schema = json_decode(
            $this->generator->generate(
                ['customerAccountManagementV1'],
                'https',
                'localhost',
                '/async/schema/V1?services=customerAccountManagementV1'
            ),
            true
        );

        // Ensure that the correct HTTP response has been described in the schema.
        $this->assertArrayHasKey(
            '202',
            $schema['paths']['/V1/customers']['post']['responses']
        );

        $this->assertArrayNotHasKey(
            '200',
            $schema['paths']['/V1/customers']['post']['responses']
        );

        // 202 Response should not apply to GET requests.
        $this->assertArrayNotHasKey(
            '/V1/customers/me/shippingAddress',
            $schema['paths']
        );

        // Ensure that the response type has been replaced with the async version.
        $this->assertEquals(
            '#/definitions/asynchronous-operations-data-async-response-interface',
            $schema['paths']['/V1/customers']['post']['responses']['202']['schema']['$ref']
        );

        // Ensure that the base path output correctly
        $this->assertEquals('/async', $schema['basePath']);
    }
}
