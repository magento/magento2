<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Fixture;

use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;

/**
 * Design Config fixture
 *
 * Example 1: Basic usage
 *
 * ```php
 *    #[
 *        DataFixture(
 *            DesignConfigFixture::class,
 *            [
 *                'scope_type' => ScopeInterface::SCOPE_WEBSITES,
 *                'scope_id' => 1,
 *                'data' => [
 *                    [
 *                        'path' => 'design/footer/absolute_footer',
 *                        'value' => 'test footer'
 *                    ]
 *                ]
 *            ]
 *        )
 *    ]
 *    public function testConfig(): void
 *    {
 *
 *    }
 * ```
 */
class DesignConfig implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'scope_type' => ScopeInterface::SCOPE_STORES,
        'scope_id' => 1,
        'data' => []
    ];

    /**
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param State $state
     */
    public function __construct(
        private readonly DesignConfigRepositoryInterface $designConfigRepository,
        private readonly DataObjectFactory $dataObjectFactory,
        private readonly State $state
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $data['orig_data'] = $this->applyConfig(
            $data['scope_type'],
            $data['scope_id'],
            array_column($data['data'], 'value', 'path')
        );
        return $this->dataObjectFactory->create(['data' => $data]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->applyConfig(
            $data['scope_type'],
            $data['scope_id'],
            array_column($data['orig_data'], 'value', 'path')
        );
    }

    /**
     * Save config
     *
     * @param string $scopeType
     * @param int $scopeId
     * @param array $data
     * @return array
     */
    private function applyConfig(string $scopeType, int $scopeId, array $data): array
    {
        $designConfig = $this->designConfigRepository->getByScope($scopeType, $scopeId);
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        $origData = [];
        foreach ($fieldsData as $fieldData) {
            if (array_key_exists($fieldData->getPath(), $data)) {
                $origData[] = [
                    'path' => $fieldData->getPath(),
                    'value' => $fieldData->getValue()
                ];
                $fieldData->setValue($data[$fieldData->getPath()]);
            }
        }
        $currentArea = $this->state->getAreaCode();
        $this->state->setAreaCode('adminhtml');
        $designConfig->setScope($scopeType);
        $designConfig->setScopeId($scopeId);
        $this->designConfigRepository->save($designConfig);
        $this->state->setAreaCode($currentArea);
        return $origData;
    }
}
