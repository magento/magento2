<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Controller;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\GraphQl\HttpHeaderProcessorInterface;
use Magento\Framework\GraphQl\HttpRequestProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Request;
use Magento\GraphQl\Controller\GraphQl;

/**
 * Class GraphQlTest
 *
 * @magentoAppArea graphql
 * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
 */

class GraphQlControllerTest extends \PHPUnit\Framework\TestCase
{
    /** @var  string */
    private $mageMode;

    const CONTENT_TYPE = 'application/json';


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

   /** @var \Magento\Framework\App\Request\Http $request  */
    private $request;

    /**
     * @var GraphQl $graphql
     */
    private $graphql;

    /** @var  array */
    private $serverArray;


    /** @var SerializerInterface  */
    private $jsonSerializer;

    protected function setUp()
    {
        // parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     *  Tests if a graphql schema is generated and request is dispatched and response generated
     */
    public function testDispatch()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        /** @var ProductInterface $product */
        $product = $productRepository->get('simple');

        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "simple"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;
        $postData = [
            'query' => $query,
            'variables'=> null,
            'operationName'=> null
        ];
        /** @var Http $request */
        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setPathInfo('/graphql');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
                    ->addHeaders(['Content-Type' => 'application/json']
                    );
        $request->setHeaders($headers);
        $response = $this->graphql->dispatch($request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $this->assertEquals($output['data']['products']['items'][0]['id'], $product->getId());
        $this->assertEquals($output['data']['products']['items'][0]['sku'], $product->getSku());
        $this->assertEquals($output['data']['products']['items'][0]['name'], $product->getName());
    }
}
