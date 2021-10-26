<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestModuleGraphQlBackpressure\Model\TestServiceResolver;
use Magento\Framework\App\Request\Http as HttpRequest;

class BackpressureTest extends AbstractController
{
    /**
     * @var TestServiceResolver
     */
    private $testResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testResolver = Bootstrap::getObjectManager()->get(TestServiceResolver::class);
        $this->testResolver->resetCounter();
    }

    /**
     * Verify that backpressure is enforced.
     *
     * @return void
     */
    public function testBackpressure(): void
    {
        $nOfReqs = 6;

        $query
            = <<<QUERY
{
  testGraphqlRead {
    str
  }
}
QUERY;
        $postData = [
            'query' => $query,
            'variables' => null,
            'operationName' => null
        ];
        for ($i = 0; $i < $nOfReqs; $i++) {
            /** @var HttpRequest $request */
            $request = $this->getRequest();
            $request->setPathInfo('/graphql');
            $request->setMethod('POST');
            $request->setContent(json_encode($postData));
            $request->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
            $this->dispatch('/graphql');
        }

        $this->assertGreaterThan(0, $this->testResolver->getCounter());
        $this->assertLessThan($nOfReqs, $this->testResolver->getCounter());
    }
}
