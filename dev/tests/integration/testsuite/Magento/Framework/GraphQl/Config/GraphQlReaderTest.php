<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Config;
use Magento\Framework\GraphQl\Schema\SchemaGenerator;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;

/**
 * Tests the entire process of generating a schema from a given SDL and processing a request/query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea graphql
 */
class GraphQlReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var Config */
    private $configModel;

    /** @var  GraphQl */
    private $graphQlController;

    /** @var  ObjectManagerInterface */
    private $objectManager;

    /** @var  SerializerInterface */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Cache $cache */
        $cache = $this->objectManager->get(Cache::class);
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            \Magento\Framework\Config\FileResolverInterface::class
        )->disableOriginalConstructor()->getMock();
        $fileList = [
            file_get_contents(__DIR__ . '/../_files/schemaA.graphqls'),
            file_get_contents(__DIR__ . '/../_files/schemaB.graphqls')
        ];
        $fileResolverMock->expects($this->any())->method('get')->willReturn($fileList);
        $graphQlReader = $this->objectManager->create(
            \Magento\Framework\GraphQlSchemaStitching\GraphQlReader::class,
            ['fileResolver' => $fileResolverMock]
        );
        $reader = $this->objectManager->create(
            // phpstan:ignore
            \Magento\Framework\GraphQlSchemaStitching\Reader::class,
            ['readers' => ['graphql_reader' => $graphQlReader]]
        );
        $data = $this->objectManager->create(
            // phpstan:ignore
            \Magento\Framework\GraphQl\Config\Data ::class,
            ['reader' => $reader]
        );
        $this->configModel = $this->objectManager->create(
            \Magento\Framework\GraphQl\Config::class,
            ['data' => $data]
        );
        $outputMapper = $this->objectManager->create(
            \Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper::class,
            ['config' => $this->configModel]
        );
        $schemaGenerator = $this->objectManager->create(
            SchemaGenerator::class,
            ['outputMapper' => $outputMapper]
        );
        $this->graphQlController = $this->objectManager->create(
            GraphQl::class,
            ['schemaGenerator' => $schemaGenerator]
        );
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDispatchIntrospectionWithCustomSDL()
    {
        $query
            = <<<QUERY
 query IntrospectionQuery {
  __schema {
    queryType { name }
    types {
      ...FullType
    }
  }
}

fragment FullType on __Type {
  kind
  name
  description
  fields(includeDeprecated: true) {
    name
    description
    args {
      ...InputValue
    }
    type {
      ...TypeRef
    }
    isDeprecated
    deprecationReason
  }
  inputFields {
    ...InputValue
  }
  interfaces {
    ...TypeRef
  }
  enumValues(includeDeprecated: true) {
    name
    description
    isDeprecated
  }
  possibleTypes {
    ...TypeRef
  }
}

fragment InputValue on __InputValue {
  name
  description
  type { ...TypeRef }
  defaultValue
}

fragment TypeRef on __Type {
  kind
  name
  ofType {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
              ofType {
                kind
                name
              }
            }
          }
        }
      }
    }
  }
}


QUERY;
        $postData = [
            'query'         => $query,
            'variables'     => null,
            'operationName' => 'IntrospectionQuery'
        ];
        /** @var Http $request */
        $request = $this->objectManager->get(Http::class);
        $request->setPathInfo('/graphql');
        $request->setMethod('POST');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Laminas\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $request->setHeaders($headers);

        $response = $this->graphQlController->dispatch($request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $expectedOutput = require __DIR__ . '/../_files/schema_response_sdl_description.php';

        $schemaResponseFields = $output['data']['__schema']['types'];
        $schemaResponseFieldsFirstHalf = array_slice($schemaResponseFields, 0, 25);
        $schemaResponseFieldsSecondHalf = array_slice($schemaResponseFields, -21, 21);
        $mergedSchemaResponseFields = array_merge($schemaResponseFieldsFirstHalf, $schemaResponseFieldsSecondHalf);

        foreach ($expectedOutput as $searchTerm) {
            $sortFields = ['inputFields', 'fields'];
            foreach ($sortFields as $sortField) {
                isset($searchTerm[$sortField]) && is_array($searchTerm[$sortField])
                    ? usort(
                        $searchTerm[$sortField],
                        function ($a, $b) {
                            $cmpField = 'name';
                            return isset($a[$cmpField]) && isset($b[$cmpField])
                            ? strcmp($a[$cmpField], $b[$cmpField]) : 0;
                        }
                    ) : null;
            }

            $this->assertTrue(
                (in_array($searchTerm, $mergedSchemaResponseFields)),
                'Missing type in the response'
            );
        }
        //Checks to make sure that the given description exists in the expectedOutput array
        $this->assertArrayHasKey(
            
                array_search(
                    'Comment for empty PhysicalProductInterface',
                    array_column($expectedOutput, 'description')
                ),
                $expectedOutput
            
        );
        $this->assertArrayHasKey(
            
                array_search(
                    'Comment for empty Enum',
                    array_column($expectedOutput, 'description')
                ),
                $expectedOutput
            
        );
        $this->assertArrayHasKey(
            
                array_search(
                    'Comment for SearchResultPageInfo',
                    array_column($expectedOutput, 'description')
                ),
                $expectedOutput
            
        );
    }
}
