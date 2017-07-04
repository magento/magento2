<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model;

use Magento\Config\Model\Config\BackendFactory;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\RuntimeException;

/**
 * Creates a prepared instance of Value.
 *
 * @see ValueInterface
 * @api
 */
class PreparedValueFactory
{
    /**
     * The scope resolver pool.
     *
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * The manager for system configuration structure.
     *
     * @var StructureFactory
     */
    private $structureFactory;

    /**
     * The factory for configuration value objects.
     *
     * @see ValueInterface
     * @var BackendFactory
     */
    private $valueFactory;

    /**
     * The scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * The scope type normalizer.
     *
     * @var ScopeTypeNormalizer
     */
    private $scopeTypeNormalizer;

    /**
     * @param ScopeResolverPool $scopeResolverPool The scope resolver pool
     * @param StructureFactory $structureFactory The manager for system configuration structure
     * @param BackendFactory $valueFactory The factory for configuration value objects
     * @param ScopeConfigInterface $config The scope configuration
     * @param ScopeTypeNormalizer $scopeTypeNormalizer The scope type normalizer
     */
    public function __construct(
        ScopeResolverPool $scopeResolverPool,
        StructureFactory $structureFactory,
        BackendFactory $valueFactory,
        ScopeConfigInterface $config,
        ScopeTypeNormalizer $scopeTypeNormalizer
    ) {
        $this->scopeResolverPool = $scopeResolverPool;
        $this->structureFactory = $structureFactory;
        $this->valueFactory = $valueFactory;
        $this->config = $config;
        $this->scopeTypeNormalizer = $scopeTypeNormalizer;
    }

    /**
     * Returns instance of Value with defined properties.
     *
     * @param string $path The configuration path in format section/group/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string|int|null $scopeCode The scope code
     * @return ValueInterface
     * @throws RuntimeException If Value can not be created
     * @see ValueInterface
     */
    public function create($path, $value, $scope, $scopeCode = null)
    {
        try {
            /** @var Structure $structure */
            $structure = $this->structureFactory->create();
            /** @var Structure\ElementInterface $field */
            $field = $structure->getElementByConfigPath($path);
            $configPath = $path;
            /** @var string $backendModelName */
            if ($field instanceof Structure\Element\Field && $field->hasBackendModel()) {
                $backendModelName = $field->getData()['backend_model'];
                $configPath = $field->getConfigPath() ?: $path;
            } else {
                $backendModelName = ValueInterface::class;
            }
            /** @var ValueInterface $backendModel */
            $backendModel = $this->valueFactory->create(
                $backendModelName,
                ['config' => $this->config]
            );

            if ($backendModel instanceof Value) {
                $scopeId = 0;

                $scope = $this->scopeTypeNormalizer->normalize($scope);

                if ($scope !== ScopeInterface::SCOPE_DEFAULT) {
                    $scopeId = $this->scopeResolverPool->get($scope)
                        ->getScope($scopeCode)
                        ->getId();
                }

                if ($field instanceof Structure\Element\Field) {
                    $groupPath = $field->getGroupPath();
                    $group = $structure->getElement($groupPath);
                    $backendModel->setField($field->getId());
                    $backendModel->setGroupId($group->getId());
                    $backendModel->setFieldConfig($field->getData());
                }

                $backendModel->setPath($configPath);
                $backendModel->setScope($scope);
                $backendModel->setScopeId($scopeId);
                $backendModel->setScopeCode($scopeCode);
                $backendModel->setValue($value);
            }

            return $backendModel;
        } catch (\Exception $exception) {
            throw new RuntimeException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
