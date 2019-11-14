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
     * @magentoConfigFixture default_store csp/policies/storefront/plugin_types/types/flash application/x-shockwave-flash
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
        $childScrChecked = false;
        $childScr2Checked = false;
        $connectScrChecked = false;
        $defaultScrChecked = false;
        $fontScrChecked = false;
        $frameScrChecked = false;
        $imgScrChecked = false;
        $manifestScrChecked = false;
        $mediaScrChecked = false;
        $objectScrChecked = false;
        $scriptScrChecked = false;
        $styleScrChecked = false;
        $baseUriChecked = false;
        $pluginTypesChecked = false;
        $sandboxChecked = false;
        $formActionChecked = false;
        $frameAncestorsChecked = false;
        $blockAllMixedChecked = false;
        $upgradeChecked = false;

        $this->assertNotEmpty($policies);
        /** @var PolicyInterface|FetchPolicy|FlagPolicy|SandboxPolicy|PluginTypesPolicy $policy */
        foreach ($policies as $policy) {
            switch ($policy->getId())
            {
                case 'child-src':
                    if ($policy->isEvalAllowed()) {
                        $childScr2Checked = true;
                    } else {
                        $childScrChecked = !$policy->isNoneAllowed()
                            && $policy->getHostSources() == ['http://magento.com', 'http://devdocs.magento.com']
                            && $policy->getSchemeSources() == ['http']
                            && $policy->isSelfAllowed()
                            && !$policy->isEvalAllowed()
                            && $policy->isDynamicAllowed()
                            && $policy->getHashes() == []
                            && $policy->getNonceValues() == []
                            && $policy->isInlineAllowed();
                    }
                    break;
                case 'connect-src':
                    $connectScrChecked = $policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && !$policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'default-src':
                    $defaultScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == ['http://magento.com', 'http://devdocs.magento.com']
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'font-src':
                    $fontScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'frame-src':
                    $frameScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && $policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'img-src':
                    $imgScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'manifest-src':
                    $manifestScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'media-src':
                    $mediaScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'object-src':
                    $objectScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'script-src':
                    $scriptScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'style-src':
                    $styleScrChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'base-uri':
                    $baseUriChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'plugin-types':
                    $pluginTypesChecked = $policy->getTypes()
                        == ['application/x-shockwave-flash', 'application/x-java-applet'];
                    break;
                case 'sandbox':
                    $sandboxChecked = $policy->isFormAllowed()
                        && $policy->isModalsAllowed()
                        && $policy->isOrientationLockAllowed()
                        && $policy->isPointerLockAllowed()
                        && !$policy->isPopupsAllowed()
                        && !$policy->isPopupsToEscapeSandboxAllowed()
                        && $policy->isPresentationAllowed()
                        && $policy->isSameOriginAllowed()
                        && $policy->isScriptsAllowed()
                        && $policy->isTopNavigationAllowed()
                        && $policy->isTopNavigationByUserActivationAllowed();
                    break;
                case 'form-action':
                    $formActionChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'frame-ancestors':
                    $frameAncestorsChecked = !$policy->isNoneAllowed()
                        && $policy->getHostSources() == []
                        && $policy->getSchemeSources() == []
                        && $policy->isSelfAllowed()
                        && !$policy->isEvalAllowed()
                        && !$policy->isDynamicAllowed()
                        && $policy->getHashes() == []
                        && $policy->getNonceValues() == []
                        && !$policy->isInlineAllowed();
                    break;
                case 'block-all-mixed-content':
                    $blockAllMixedChecked = $policy instanceof FlagPolicy;
                    break;
                case 'upgrade-insecure-requests':
                    $upgradeChecked = $policy instanceof FlagPolicy;
                    break;
            }
        }

        $this->assertTrue($childScrChecked);
        $this->assertTrue($childScr2Checked);
        $this->assertTrue($connectScrChecked);
        $this->assertTrue($defaultScrChecked);
        $this->assertTrue($fontScrChecked);
        $this->assertTrue($frameScrChecked);
        $this->assertTrue($imgScrChecked);
        $this->assertTrue($manifestScrChecked);
        $this->assertTrue($mediaScrChecked);
        $this->assertTrue($objectScrChecked);
        $this->assertTrue($scriptScrChecked);
        $this->assertTrue($styleScrChecked);
        $this->assertTrue($baseUriChecked);
        $this->assertTrue($pluginTypesChecked);
        $this->assertTrue($sandboxChecked);
        $this->assertTrue($formActionChecked);
        $this->assertTrue($frameAncestorsChecked);
        $this->assertTrue($blockAllMixedChecked);
        $this->assertTrue($upgradeChecked);
    }
}
