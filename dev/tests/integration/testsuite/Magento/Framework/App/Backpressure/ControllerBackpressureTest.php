<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestModuleControllerBackpressure\Controller\Read\Read;

/**
 * @magentoAppArea frontend
 */
class ControllerBackpressureTest extends AbstractController
{
    /**
     * @var Read
     */
    private $index;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->index = Bootstrap::getObjectManager()->get(Read::class);
        $this->index->resetCounter();
    }

    /**
     * Verify that backpressure is enforced for controllers.
     *
     * @return void
     */
    public function testBackpressure(): void
    {
        $nOfReqs = 6;

        for ($i = 0; $i < $nOfReqs; $i++) {
            $this->dispatch('testbackpressure/read/read');
        }

        $counter = json_decode($this->getResponse()->getBody(), true)['counter'];
        $this->assertGreaterThan(0, $counter);
        $this->assertLessThan($nOfReqs, $counter);
    }
}
