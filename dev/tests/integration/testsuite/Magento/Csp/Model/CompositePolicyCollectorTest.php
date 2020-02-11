<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Csp\Model\Policy\FlagPolicy;
use Magento\Csp\Model\Policy\PluginTypesPolicy;
use Magento\Csp\Model\Policy\SandboxPolicy;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test that composite collector properly calls other collectors and merges results.
 */
class CompositePolicyCollectorTest extends TestCase
{
    /**
     * Create mock collectors that will populate policies.
     *
     * @return PolicyCollectorInterface[]
     */
    private function createMockCollectors(): array
    {
        $mockCollector1 = $this->getMockForAbstractClass(PolicyCollectorInterface::class);
        $mockCollector1->method('collect')
            ->willReturnCallback(
                function (array $prevPolicies) {
                    return array_merge(
                        $prevPolicies,
                        [
                            new FetchPolicy(
                                'script-src',
                                false,
                                ['https://magento.com'],
                                ['https'],
                                true,
                                false,
                                true,
                                ['569403695046645'],
                                ['B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF8=' => 'sha256'],
                                false,
                                true
                            ),
                            new FetchPolicy('script-src', false, ['https://devdocs.magento.com']),
                            new FlagPolicy('upgrade-insecure-requests'),
                            new PluginTypesPolicy(['application/x-shockwave-flash']),
                            new SandboxPolicy(false, true, false, true, false, true, false, true, false, true, false)
                        ]
                    );
                }
            );
        $mockCollector2 = $this->getMockForAbstractClass(PolicyCollectorInterface::class);
        $mockCollector2->method('collect')
            ->willReturnCallback(
                function (array $prevPolicies) {
                    return array_merge(
                        $prevPolicies,
                        [
                            new FetchPolicy(
                                'script-src',
                                true,
                                ['http://magento.com'],
                                ['http'],
                                false,
                                false,
                                false,
                                ['5694036950466451'],
                                ['B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF7=' => 'sha256'],
                                true,
                                false
                            ),
                            new FetchPolicy('default-src', false, [], [], true),
                            new FlagPolicy('upgrade-insecure-requests'),
                            new PluginTypesPolicy(['application/x-java-applet']),
                            new SandboxPolicy(true, false, true, false, true, false, true, false, true, false, false)
                        ]
                    );
                }
            );

        return [$mockCollector1, $mockCollector2];
    }

    /**
     * Test collect method.
     *
     * Supply fake collectors, check results.
     *
     * @return void
     */
    public function testCollect(): void
    {
        /** @var CompositePolicyCollector $collector */
        $collector = Bootstrap::getObjectManager()->create(
            CompositePolicyCollector::class,
            ['collectors' => $this->createMockCollectors()]
        );

        $collected = $collector->collect([]);
        /** @var FetchPolicy[]|FlagPolicy[]|PluginTypesPolicy[]|SandboxPolicy[] $policies */
        $policies = [];
        /** @var \Magento\Csp\Api\Data\PolicyInterface $policy */
        foreach ($collected as $policy) {
            $policies[$policy->getId()] = $policy;
        }
        //Comparing resulting policies
        $this->assertArrayHasKey('script-src', $policies);
        $this->assertTrue($policies['script-src']->isNoneAllowed());
        $this->assertTrue($policies['script-src']->isSelfAllowed());
        $this->assertFalse($policies['script-src']->isInlineAllowed());
        $this->assertTrue($policies['script-src']->isEvalAllowed());
        $this->assertTrue($policies['script-src']->isDynamicAllowed());
        $this->assertTrue($policies['script-src']->areEventHandlersAllowed());
        $foundHosts = $policies['script-src']->getHostSources();
        $hosts = ['http://magento.com', 'https://magento.com', 'https://devdocs.magento.com'];
        sort($foundHosts);
        sort($hosts);
        $this->assertEquals($hosts, $foundHosts);
        $foundSchemes = $policies['script-src']->getSchemeSources();
        $schemes = ['https', 'http'];
        sort($foundSchemes);
        sort($schemes);
        $this->assertEquals($schemes, $foundSchemes);
        $foundNonceValues = $policies['script-src']->getNonceValues();
        $nonceValues = ['5694036950466451', '569403695046645'];
        sort($foundNonceValues);
        sort($nonceValues);
        $this->assertEquals($nonceValues, $foundNonceValues);
        $foundHashes = $policies['script-src']->getHashes();
        $hashes = [
            'B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF7=' => 'sha256',
            'B2yPHKaXnvFWtRChIbabYmUBFZdVfKKXHbWtWidDVF8=' => 'sha256'
        ];
        $this->assertEquals($hashes, $foundHashes);

        $this->assertArrayHasKey('default-src', $policies);
        $this->assertFalse($policies['default-src']->isNoneAllowed());
        $this->assertTrue($policies['default-src']->isSelfAllowed());
        $this->assertFalse($policies['default-src']->isInlineAllowed());
        $this->assertFalse($policies['default-src']->isEvalAllowed());
        $this->assertFalse($policies['default-src']->isDynamicAllowed());
        $this->assertFalse($policies['default-src']->areEventHandlersAllowed());
        $this->assertEmpty($policies['default-src']->getHashes());
        $this->assertEmpty($policies['default-src']->getNonceValues());
        $this->assertEmpty($policies['default-src']->getHostSources());
        $this->assertEmpty($policies['default-src']->getSchemeSources());

        $this->assertArrayHasKey('upgrade-insecure-requests', $policies);
        $this->assertInstanceOf(FlagPolicy::class, $policies['upgrade-insecure-requests']);

        $this->assertArrayHasKey('plugin-types', $policies);
        $types = ['application/x-java-applet', 'application/x-shockwave-flash'];
        $foundTypes = $policies['plugin-types']->getTypes();
        sort($types);
        sort($foundTypes);
        $this->assertEquals($types, $foundTypes);

        $this->assertArrayHasKey('sandbox', $policies);
        $this->assertTrue($policies['sandbox']->isFormAllowed());
        $this->assertTrue($policies['sandbox']->isModalsAllowed());
        $this->assertTrue($policies['sandbox']->isOrientationLockAllowed());
        $this->assertTrue($policies['sandbox']->isPointerLockAllowed());
        $this->assertTrue($policies['sandbox']->isPopupsAllowed());
        $this->assertTrue($policies['sandbox']->isPopupsToEscapeSandboxAllowed());
        $this->assertTrue($policies['sandbox']->isScriptsAllowed());
        $this->assertFalse($policies['sandbox']->isTopNavigationByUserActivationAllowed());
        $this->assertTrue($policies['sandbox']->isTopNavigationAllowed());
        $this->assertTrue($policies['sandbox']->isSameOriginAllowed());
        $this->assertTrue($policies['sandbox']->isPresentationAllowed());
    }
}
