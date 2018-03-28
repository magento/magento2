<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\TestFramework\TestCase\AbstractController;

class SchemaRequestProcessorTest extends AbstractController
{
    /**
     * Test that the rest controller returns the correct schema response.
     *
     * @param string $path
     * @dataProvider schemaRequestProvider
     */
    public function testSchemaRequest($path)
    {
        $this->dispatch($path);
        $schema = $this->getResponse()->getBody();

        // Check that an HTTP 200 response status is visible in the schema.
        $this->assertRegExp('/200 Success/', $schema);
    }

    /**
     * @return array
     */
    public function schemaRequestProvider()
    {
        return [
            ['rest/schema'],
            ['rest/schema?services=all'],
            ['rest/all/schema?services=all'],
            ['rest/default/schema?services=all'],
            ['rest/schema?services=all'],
        ];
    }
}
