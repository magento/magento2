<?php

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\App\Cache;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Model\SchemaGenerator;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests the entire process of generating a schema from a given SDL and processing a request/query
 *
 * @package Magento\Framework\GraphQl\Config
 * @magentoAppArea graphql
 */
class GraphQlReaderTest extends \PHPUnit\Framework\TestCase
{

    /** @var Config */
    private $model;

    /** @var  GraphQl */
    private $graphQlController;

    /** @var  ObjectManagerInterface */
    private $objectManager;

    /** @var  SerializerInterface */
    private $jsonSerializer;


    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Cache $cache */
        $cache = $this->objectManager->get(Cache::class);
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            \Magento\Framework\Config\FileResolverInterface::class
        )->disableOriginalConstructor()->getMock();
       // $fileList = [file_get_contents(__DIR__ . '/../_files/schemaA.graphql')];
        $fileList = [
            file_get_contents(__DIR__ . '/../_files/schemaA.graphql'),
            file_get_contents(__DIR__ . '/../_files/schemaB.graphql')
        ];
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $graphQlReader = $this->objectManager->create(
            \Magento\Framework\GraphQl\Config\GraphQlReader::class,
            ['fileResolver' => $fileResolverMock]
        );
        $reader = $this->objectManager->create(
            \Magento\Framework\GraphQl\Config\Reader::class,
            ['readers' => ['graphQlReader' => $graphQlReader]]
        );
        $data = $this->objectManager->create(
            \Magento\Framework\GraphQl\Config\Data ::class,
            ['reader' => $reader]
        );
        $this->model = $this->objectManager->create(
            \Magento\Framework\GraphQl\Config\Config::class,
            ['data' => $data]
        );
        $graphQlSchemaProvider = $this->objectManager->create(
            \Magento\Framework\GraphQl\SchemaProvider::class,
            ['config' =>$this->model]
        );
        $typeGenerator = $this->objectManager->create(
            \Magento\GraphQl\Model\Type\Generator::class,
            ['schemaProvider' => $graphQlSchemaProvider]
    );
        $schemaGenerator = $this->objectManager->create(
            SchemaGenerator::class,
            ['typeGenerator' => $typeGenerator]
        );
        $this->graphQlController = $this->objectManager->create(
             GraphQl::class,
            ['schemaGenerator' => $schemaGenerator]
        );
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
    }

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
        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setPathInfo('/graphql');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $request->setHeaders($headers);
        $response = $this->graphQlController->dispatch($request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $expectedOutput = require __DIR__ . '/../_files/schema_with_description_sdl.php';
        $schemaResponseFields = $output['data']['__schema']['types'];
        foreach ($expectedOutput as $searchTerm) {
            $this->assertTrue(
                (in_array($searchTerm, $schemaResponseFields)),
                'Missing type in the response'
            );
        }
    }
}
