<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\GraphQlSchemaStitching;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Test of the stitching of graphql schemas together
 */
class GraphQlReaderTest extends TestCase
{
    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GraphQlReader|MockObject
     */
    private $graphQlReader;

    protected function setUp(): void
    {
        /** @var ObjectManagerInterface $objectManager */
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->graphQlReader = $this->objectManager->create(
            GraphQlReader::class
        );
    }

    /**
     * This test ensures that the global graphql schemas have all the required dependencies and can be stitched together
     *
     * The $results variables contains the actual schema as it will be on a production site which will vary per each
     * update of magento, so asserting the array matches the entire schema does not make full sense here as any change
     * in graphql in any magento module would break the test.
     *
     * Testing this way means we do not need to store the module meta data that was introduced in
     * https://github.com/magento/magento2/pull/28747 which means we can greatly improve the performance of this
     */
    public function testStitchGlobalGraphQLSchema()
    {
        $results = $this->graphQlReader->read('global');

        $this->assertArrayHasKey('Price', $results);
        $this->assertArrayHasKey('Query', $results);
        $this->assertArrayHasKey('Mutation', $results);
        $this->assertArrayHasKey('ProductInterface', $results);
        $this->assertArrayHasKey('SimpleProduct', $results);
    }
}
