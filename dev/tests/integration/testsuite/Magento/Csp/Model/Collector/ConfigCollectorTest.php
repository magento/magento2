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
    public function setUp()
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
                ['http'],
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
            'font-src' => new FetchPolicy('font-src', false, [], [], true),
            'frame-src' => new FetchPolicy('frame-src', false, [], [], true, false, false, [], [], true),
            'img-src' => new FetchPolicy('img-src', false, [], [], true),
            'manifest-src' => new FetchPolicy('manifest-src', false, [], [], true),
            'media-src' => new FetchPolicy('media-src', false, [], [], true),
            'object-src' => new FetchPolicy('object-src', false, [], [], true),
            'script-src' => new FetchPolicy('script-src', false, [], [], true),
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
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/policy_id default-src
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/default_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/policy_id child-src
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/hosts/example http://magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/hosts/example2 http://devdocs.magento.com
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/inline 1
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/schemes/scheme1 http
     * @magentoConfigFixture default_store csp/policies/storefront/child_src/dynamic 1
     * @magentoConfigFixture default_store csp/policies/storefront/child_src2/policy_id child-src
     * @magentoConfigFixture default_store csp/policies/storefront/child_src2/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/child_src2/eval 1
     * @magentoConfigFixture default_store csp/policies/storefront/connect_src/policy_id connect-src
     * @magentoConfigFixture default_store csp/policies/storefront/connect_src/none 1
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/policy_id font-src
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/font_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/frame_src/policy_id frame-src
     * @magentoConfigFixture default_store csp/policies/storefront/frame_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/frame_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/frame_src/dynamic 1
     * @magentoConfigFixture default_store csp/policies/storefront/img_src/policy_id img-src
     * @magentoConfigFixture default_store csp/policies/storefront/img_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/img_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/manifest_src/policy_id manifest-src
     * @magentoConfigFixture default_store csp/policies/storefront/manifest_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/manifest_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/media_src/policy_id media-src
     * @magentoConfigFixture default_store csp/policies/storefront/media_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/media_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/object_src/policy_id object-src
     * @magentoConfigFixture default_store csp/policies/storefront/object_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/object_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/policy_id script-src
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/script_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/policy_id base-uri
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/base_uri/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/style_src/policy_id style-src
     * @magentoConfigFixture default_store csp/policies/storefront/style_src/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/style_src/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/form_action/policy_id form-action
     * @magentoConfigFixture default_store csp/policies/storefront/form_action/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/form_action/self 1
     * @magentoConfigFixture default_store csp/policies/storefront/frame_ancestors/policy_id frame-ancestors
     * @magentoConfigFixture default_store csp/policies/storefront/frame_ancestors/none 0
     * @magentoConfigFixture default_store csp/policies/storefront/frame_ancestors/self 1
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
     * @magentoConfigFixture default_store csp/policies/storefront/upgrade/policy_id upgrade-insecure-requests
     * @return void
     */
    public function testCollecting(): void
    {
        $policies = $this->collector->collect([]);
        $checked = [];
        $expectedPolicies = $this->getExpectedPolicies();

        $this->assertNotEmpty($policies);
        /** @var PolicyInterface $policy */
        foreach ($policies as $policy) {
            $id = $policy->getId();
            if ($id === 'child-src' && $policy->isEvalAllowed()) {
                $id = 'child-src2';
            }
            $this->assertEquals($expectedPolicies[$id], $policy);
            $checked[] = $id;
        }
        $expectedIds = array_keys($expectedPolicies);
        $this->assertEquals(sort($expectedIds), sort($checked));
    }
}
