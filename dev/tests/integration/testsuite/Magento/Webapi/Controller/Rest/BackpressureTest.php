<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Controller\Rest;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestModuleWebapiBackpressure\Model\TestReadService;

class BackpressureTest extends AbstractController
{
    /**
     * @var TestReadService
     */
    private $testReadService;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testReadService = Bootstrap::getObjectManager()->get(TestReadService::class);
        $this->testReadService->resetCounter();
    }

    /**
     * Verify that backpressure works for web APIs.
     *
     * @return void
     */
    public function testBackpressure(): void
    {
        $nOfReqs = 6;
        for ($i = 0; $i < $nOfReqs; $i++) {
            $this->dispatch('rest/V1/test-module-webapi-backpressure/read');
        }

        //Some of the requests must have been throttled.
        $this->assertLessThan($nOfReqs, $this->testReadService->getCounter());
    }
}
