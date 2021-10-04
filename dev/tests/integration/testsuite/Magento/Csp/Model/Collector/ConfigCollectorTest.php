<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Csp\Model\Policy\FlagPolicy;
use Magento\Csp\Model\Policy\PluginTypesPolicy;
use Magento\Csp\Model\Policy\SandboxPolicy;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test collecting policies from Magento config.
 */
class ConfigCollectorTest extends TestCase
{
    /**
     * @var ConfigCollector
     */
    private $collector;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->collector = Bootstrap::getObjectManager()->get(ConfigCollector::class);
    }

    /**
     * Create expected policy objects.
     *
     * @return PolicyInterface[]
     */
    private function getExpectedPolicies(): array
    {
        return [
            'child-src' => new FetchPolicy(
                'child-src',
                false,
                ['http://magento.com', 'http://devdocs.magento.com'],
                ['http', 'https', 'blob'],
                true,
                true,
                false,
                [],
                [],
                true
            ),
            'child-src2' => new FetchPolicy('child-src', false, [], [], false, false, true),
            'connect-src' => new FetchPolicy('connect-src'),
            'default-src' => new FetchPolicy(
                'default-src',
                false,
                ['http://magento.com', 'http://devdocs.magento.com'],
                [],
                true
            ),
            'font-src' => new FetchPolicy('font-src', false, [], ['data'], true),
            'frame-src' => new FetchPolicy('frame-src', false, [], [], true, false, false, [], [], true),
            'img-src' => new FetchPolicy('img-src', false, [], ['data'], true),
            'manifest-src' => new FetchPolicy('manifest-src', false, [], [], true),
            'media-src' => new FetchPolicy('media-src', false, [], [], true),
            'object-src' => new FetchPolicy('object-src', false, [], [], true),
            'script-src' => new FetchPolicy('script-src', false, [], [], true, false, false, [], [], false, true),
            'style-src' => new FetchPolicy('style-src', false, [], [], true),
            'base-uri' => new FetchPolicy('base-uri', false, [], [], true),
            'plugin-types' => new PluginTypesPolicy(
                ['application/x-shockwave-flash', 'application/x-java-applet']
            ),
            'sandbox' => new SandboxPolicy(true, true, true, true, false, false, true, true, true, true, true),
            'form-action' => new FetchPolicy('form-action', false, [], [], true),
            'frame-ancestors' => new FetchPolicy('frame-ancestors', false, [], [], true),
            'block-all-mixed-content' => new FlagPolicy('block-all-mixed-content'),
            'upgrade-insecure-requests' => new FlagPolicy('upgrade-insecure-requests')
        ];
    }

    /**
     * Test initiating policies from config.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store csp/policies/storefront/default/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/storefront/default/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/default/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/default/eval 0
     * @magentoConfigFixture default_store csp/policies/storefront/default/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/children/policy_id child-src
     * @magentoConfigFixture default_store csp/policies/storefront/children/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/children/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/children/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/children/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/children/inline 1
     * @magentoConfigFixture default_store csp/policies/storefront/children/schemes/scheme1 http
     * @magentoConfigFixture default_store csp/policies/storefront/children/dynamic 1
     * @magentoConfigFixture default_store csp/policies/storefront/children-2/policy_id child-src
     * @magentoConfigFixture default_store csp/policies/storefront/children-2/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/children-2/eval 1
     * @magentoConfigFixture default_store csp/policies/storefront/connections/policy_id connect-src
     * @magentoConfigFixture default_store csp/policies/storefront/connections/none 1
     * @magentoConfigFixture default_store csp/policies/storefront/connections/self 0
     * @magentoConfigFixture default_store csp/policies/storefront/connections/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/fonts/policy_id font-src
     * @magentoConfigFixture default_store csp/policies/storefront/fonts/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/fonts/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/fonts/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/frames/policy_id frame-src
     * @magentoConfigFixture default_store csp/policies/storefront/frames/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/frames/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/frames/dynamic 1
     * @magentoConfigFixture default_store csp/policies/storefront/frames/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/images/policy_id img-src
     * @magentoConfigFixture default_store csp/policies/storefront/images/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/images/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/images/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/manifests/policy_id manifest-src
     * @magentoConfigFixture default_store csp/policies/storefront/manifests/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/manifests/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/manifests/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/media/policy_id media-src
     * @magentoConfigFixture default_store csp/policies/storefront/media/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/media/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/media/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/objects/policy_id object-src
     * @magentoConfigFixture default_store csp/policies/storefront/objects/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/objects/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/objects/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/eval 0
     * @magentoConfigFixture default_store csp/policies/storefront/scripts/event_handlers 1
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/policy_id base-uri
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/policy_id style-src
     * @magentoConfigFixture default_store csp/policies/storefront/styles/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/styles/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/styles/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/forms/policy_id form-action
     * @magentoConfigFixture default_store csp/policies/storefront/forms/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/forms/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/forms/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/frame-ancestors/policy_id frame-ancestors
     * @magentoConfigFixture default_store csp/policies/storefront/frame-ancestors/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/frame-ancestors/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/frame-ancestors/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/plugin_types/policy_id plugin-types
     * @magentoConfigFixture default_store csp/policies/storefront/plugin_types/types/fl application/x-shockwave-flash
     * @magentoConfigFixture default_store csp/policies/storefront/plugin_types/types/applet application/x-java-applet
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/policy_id sandbox
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/forms 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/modals 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/orientation 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/pointer 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/popup 0
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/popups_to_escape 0
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/presentation 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/same_origin 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/scripts 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/navigation 1
     * @magentoConfigFixture default_store csp/policies/storefront/sandbox/navigation_by_user 1
     * @magentoConfigFixture default_store csp/policies/storefront/mixed_content/policy_id block-all-mixed-content
     * @magentoConfigFixture default_store csp/policies/storefront/base/policy_id base-uri
     * @magentoConfigFixture default_store csp/policies/storefront/base/inline 0
     * @magentoConfigFixture default_store csp/policies/storefront/upgrade/policy_id upgrade-insecure-requests
     * @return void
     */
    public function testCollecting(): void
    {
        $policies = $this->collector->collect([new FlagPolicy('upgrade-insecure-requests')]);
        $expectedPolicies = $this->getExpectedPolicies();
        $this->assertNotEmpty($policies);
        $defaultPolicy = array_shift($policies);
        $this->assertEquals('upgrade-insecure-requests', $defaultPolicy->getId());
        $expectedPolicyKeys = array_keys($expectedPolicies);
        $checkedKeys = [];

        foreach ($policies as $policy) {
            $id = $policy->getId();
            $this->assertTrue(in_array($id, $expectedPolicyKeys));
            if ($id === 'child-src' && $policy->isEvalAllowed()) {
                $id = 'child-src2';
            }
            $this->assertEquals($expectedPolicies[$id]->getValue(), $policy->getValue());
            $checkedKeys[] = $id;
        }
        $this->assertEmpty(array_diff($expectedPolicyKeys, $checkedKeys));
    }
}
