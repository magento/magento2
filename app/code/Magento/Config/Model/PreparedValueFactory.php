<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\App\DeploymentConfig;

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
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param StructureFactory $structureFactory The manager for system configuration structure
     * @param ValueFactory $valueFactory The factory for configuration value objects
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        StructureFactory $structureFactory,
        ValueFactory $valueFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->structureFactory = $structureFactory;
        $this->valueFactory = $valueFactory;
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
        /** @var ValueInterface $backendModel */
        $backendModel = $field instanceof Structure\Element\Field && $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->valueFactory->create();

        if ($backendModel instanceof Value) {
            $backendModel->setPath($path);
            $backendModel->setScope($scope);
            $backendModel->setScopeId($scopeCode);
            $backendModel->setValue($value);
        }

        return $backendModel;
    }
}
