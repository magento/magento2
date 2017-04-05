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
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\Value;

/**
 * Creates a prepared instance of Value.
 *
 * @see ValueInterface
 */
class PreparedValueFactory
{
    /**
     * The deployment configuration reader.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

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
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param StructureFactory $structureFactory The manager for system configuration structure
     * @param BackendFactory $valueFactory The factory for configuration value objects
     * @param ScopeConfigInterface $config The scope configuration
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        StructureFactory $structureFactory,
        BackendFactory $valueFactory,
        ScopeConfigInterface $config
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->structureFactory = $structureFactory;
        $this->valueFactory = $valueFactory;
        $this->config = $config;
    }

    /**
     * Returns instance of Value with defined properties.
     *
     * @param string $path The configuration path in format group/section/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @return ValueInterface
     * @see ValueInterface
     * @see Value
     */
    public function create($path, $value, $scope, $scopeCode)
    {
        /** @var Structure $structure */
        $structure = $this->structureFactory->create();
        /** @var Structure\ElementInterface $field */
        $field = $this->deploymentConfig->isAvailable()
            ? $structure->getElement($path)
            : null;
        /** @var string $backendModelName */
        $backendModelName = $field instanceof Structure\Element\Field && $field->hasBackendModel()
            ? $field->getData()['backend_model']
            : ValueInterface::class;
        /** @var ValueInterface $backendModel */
        $backendModel = $this->valueFactory->create(
            $backendModelName,
            ['config' => $this->config]
        );

        if ($backendModel instanceof Value) {
            $backendModel->setPath($path);
            $backendModel->setScope($scope);
            $backendModel->setScopeId($scopeCode);
            $backendModel->setValue($value);
        }

        return $backendModel;
    }
}
