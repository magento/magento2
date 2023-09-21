<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Framework\App\Http as HttpApp;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\App\Request\HttpFactory as RequestFactory;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GraphQlStateDiff
{
    private const CONTENT_TYPE = 'application/json';

    /** @var ObjectManagerInterface */
    private ObjectManagerInterface $objectManagerBeforeTest;

    /** @var ObjectManager */
    private ObjectManager $objectManagerForTest;

    /** @var Comparator */
    private Comparator $comparator;

    /** @var RequestFactory */
    private RequestFactory $requestFactory;


    public function __construct()
    {
        $this->objectManagerBeforeTest = Bootstrap::getObjectManager();
        $this->objectManagerForTest = new ObjectManager($this->objectManagerBeforeTest);
        $this->objectManagerForTest->getFactory()->setObjectManager($this->objectManagerForTest);
        AppObjectManager::setInstance($this->objectManagerForTest);
        Bootstrap::setObjectManager($this->objectManagerForTest);
        $this->comparator = $this->objectManagerForTest->create(Comparator::class);
        $this->requestFactory = $this->objectManagerForTest->get(RequestFactory::class);
        $this->objectManagerForTest->resetStateSharedInstances();
    }

    public function getTestObjectManager()
    {
        return $this->objectManagerForTest;
    }

    public function tearDown(): void
    {
        $this->objectManagerBeforeTest->getFactory()->setObjectManager($this->objectManagerBeforeTest);
        AppObjectManager::setInstance($this->objectManagerBeforeTest);
        Bootstrap::setObjectManager($this->objectManagerBeforeTest);
    }

    public function testState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected,
        TestCase $test
    ): void {
        if (array_key_exists(1, $authInfo)) {
            $authInfo1 = $authInfo[0];
            $authInfo2 = $authInfo[1];
        } else {
            $authInfo1 = $authInfo;
            $authInfo2 = $authInfo;
        }
        if ($operationName == 'getCart') {
            $variables['id'] = $this->getMaskedQuoteIdByReservedOrderId->execute($variables['id']);
        }
        $jsonEncodedRequest = json_encode([
            'query' => $query,
            'variables' => $variables,
            'operationName' => $operationName
        ]);
        $output1 = $this->request($jsonEncodedRequest, $operationName, $authInfo1, $test,true);
        $test->assertStringContainsString($expected, $output1);
        if ($variables2) {
            $jsonEncodedRequest = json_encode([
                'query' => $query,
                'variables' => $variables2,
                'operationName' => $operationName
            ]);
        }
        $output2 = $this->request($jsonEncodedRequest, $operationName, $authInfo2, $test);
        $test->assertStringContainsString($expected, $output2);
    }

    /**
     * @param string $query
     * @param string $operationName
     * @param array $authInfo
     * @param bool $firstRequest
     * @return array
     * @throws \Exception
     */
    public function request(string $query, string $operationName, array $authInfo, TestCase $test, bool $firstRequest = false): string
    {
        $this->objectManagerForTest->resetStateSharedInstances();
        $this->comparator->rememberObjectsStateBefore($firstRequest);
        $response = $this->doRequest($query, $authInfo);
        $this->objectManagerForTest->resetStateSharedInstances();
        $this->comparator->rememberObjectsStateAfter($firstRequest);
        $result = $this->comparator->compareBetweenRequests($operationName);
        $test->assertEmpty(
            $result,
            sprintf(
                '%d objects changed state during request. Details: %s',
                count($result),
                var_export($result, true)
            )
        );
        $result = $this->comparator->compareConstructedAgainstCurrent($operationName);
        $test->assertEmpty(
            $result,
            sprintf(
                '%d objects changed state since constructed. Details: %s',
                count($result),
                var_export($result, true)
            )
        );
        return $response;
    }

    /**
     * Process the GraphQL request
     *
     * @param string $query
     * @return string
     */
    private function doRequest(string $query, array $authInfo)
    {
        $request = $this->requestFactory->create();
        $request->setContent($query);
        $request->setMethod('POST');
        $request->setPathInfo('/graphql');
        $request->getHeaders()->addHeaders(['content_type' => self::CONTENT_TYPE]);
        if ($authInfo) {
            $email = $authInfo['email'];
            $password = $authInfo['password'];
            $customerToken = $this->objectManagerForTest->get(CustomerTokenServiceInterface::class)
                ->createCustomerAccessToken($email, $password);
            $request->getHeaders()->addHeaders(['Authorization' => 'Bearer ' . $customerToken]);
        }
        $unusedResponse = $this->objectManagerForTest->create(HttpResponse::class);
        $httpApp = $this->objectManagerForTest->create(
            HttpApp::class,
            ['request' => $request, 'response' => $unusedResponse]
        );
        $actualResponse = $httpApp->launch();
        return $actualResponse->getContent();
    }
}
