<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Csp\Model\Policy\FlagPolicy;
use Magento\Framework\Exception\NotFoundException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test collecting policies from a CSP-aware controllers.
 */
class ControllerCollectorTest extends TestCase
{
    /**
     * @var ControllerCollector
     */
    private $collector;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->collector = Bootstrap::getObjectManager()->create(ControllerCollector::class);
    }

    /**
     * Test collection.
     *
     * @return void
     */
    public function testCollect(): void
    {
        $controller = new class implements CspAwareActionInterface {
            /**
             * @inheritDoc
             */
            public function execute()
            {
                throw new NotFoundException(__('Page not found.'));
            }

            /**
             * @inheritDoc
             */
            public function modifyCsp(array $appliedPolicies): array
            {
                $processed = [];
                foreach ($appliedPolicies as $policy) {
                    if ($policy instanceof FetchPolicy && $policy->getHostSources()) {
                        $policy = new FetchPolicy(
                            'default-src',
                            false,
                            array_map(
                                function ($host) {
                                    return str_replace('http://', 'https://', $host);
                                },
                                $policy->getHostSources()
                            )
                        );
                    }
                    $processed[] = $policy;
                }
                $processed[] = new FlagPolicy(FlagPolicy::POLICIES[0]);

                return $processed;
            }
        };

        $this->collector->setCurrentActionInstance($controller);
        $collected = $this->collector->collect([new FetchPolicy('default-src', false, ['http://magento.com'])]);
        $this->assertEquals(
            [new FetchPolicy('default-src', false, ['https://magento.com']), new FlagPolicy(FlagPolicy::POLICIES[0])],
            $collected
        );
    }
}
