<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Controller\Adminhtml\System\Config;

use Magento\Config\Model\Config\Loader;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks saving and updating of configuration data
 *
 * @see \Magento\Config\Controller\Adminhtml\System\Config\Save
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
    /** @var Loader */
    private $configLoader;

    /** @var ScopeResolverPool */
    private $scopeResolverPool;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configLoader = $this->_objectManager->get(Loader::class);
        $this->scopeResolverPool = $this->_objectManager->get(ScopeResolverPool::class);
    }

    /**
     * @dataProvider saveConfigDataProvider
     * @magentoDbIsolation enabled
     * @param array $params
     * @param array $post
     * @return void
     */
    public function testSaveConfig(array $params, array $post): void
    {
        $expectedPathValue = $this->prepareExpectedPathValue($params['section'], $post['groups']);
        $this->dispatchWithParams($params, $post);
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You saved the configuration.')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertPathValue($expectedPathValue);
    }

    /**
     * @return array
     */
    public function saveConfigDataProvider(): array
    {
        return [
            'configure_shipping_origin' => [
                'params' => ['section' => 'shipping'],
                'post' => [
                    'groups' => [
                        'origin' => [
                            'fields' => [
                                'country_id' => ['value' => 'CH'],
                                'region_id' => ['value' => '107'],
                                'postcode' => ['value' => '3005'],
                                'city' => ['value' => 'Bern'],
                                'street_line1' => ['value' => 'Weinbergstrasse 4'],
                                'street_line2' => ['value' => 'Suite 1'],
                            ],
                        ],
                    ],
                ],
            ],
            'configure_multi_shipping_options' => [
                'params' => ['section' => 'multishipping'],
                'post' => [
                    'groups' => [
                        'options' => [
                            'fields' => [
                                'checkout_multiple' => ['value' => '1'],
                                'checkout_multiple_maximum_qty' => ['value' => '99'],
                            ],
                        ],
                    ],
                ],
            ],
            'configure_flat_rate_shipping_method' => [
                'params' => ['section' => 'carriers'],
                'post' => [
                    'groups' => [
                        'flatrate' => [
                            'fields' => [
                                'active' => ['value' => '1'],
                                'type' => ['value' => 'I'],
                                'price' => ['value' => '5.00'],
                                'sallowspecific' => ['value' => '0'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Prepare expected path value array.
     *
     * @param string $section
     * @param array $groups
     * @return array
     */
    private function prepareExpectedPathValue(string $section, array $groups): array
    {
        foreach ($groups as $groupId => $groupData) {
            $groupPath = $section . '/' . $groupId;
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $path = $groupPath . '/' . $fieldId;
                $expectedData[$groupPath][$path] = $fieldData['value'];
            }
        }

        return $expectedData ?? [];
    }

    /**
     * Check that the values for the paths in the config data were saved successfully.
     *
     * @param array $expectedPathValue
     * @return void
     */
    private function assertPathValue(array $expectedPathValue): void
    {
        $scope = $this->scopeResolverPool->get(ScopeInterface::SCOPE_DEFAULT)->getScope();
        foreach ($expectedPathValue as $groupPath => $groupData) {
            $actualPathValue = $this->configLoader->getConfigByPath(
                $groupPath,
                $scope->getScopeType(),
                $scope->getId(),
                false
            );
            foreach ($groupData as $fieldPath => $fieldValue) {
                $this->assertArrayHasKey(
                    $fieldPath,
                    $actualPathValue,
                    sprintf('The expected config setting was not saved in the database. Path: %s', $fieldPath)
                );
                $this->assertEquals(
                    $fieldValue,
                    $actualPathValue[$fieldPath],
                    sprintf('The expected value of the config setting is not correct. Path: %s', $fieldPath)
                );
            }
        }
    }

    /**
     * Dispatch request with params
     *
     * @param array $params
     * @param array $postParams
     * @return void
     */
    private function dispatchWithParams(array $params = [], array $postParams = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST)
            ->setParams($params)
            ->setPostValue($postParams);
        $this->dispatch('backend/admin/system_config/save');
    }
}
