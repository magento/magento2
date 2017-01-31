<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigShow;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\App\Area;

/**
 * Class processes value using backend model.
 */
class ValueProcessor
{
    /**
     * System configuration structure factory.
     *
     * @var StructureFactory
     */
    private $configStructureFactory;

    /**
     * Factory of object that implement \Magento\Framework\App\Config\ValueInterface.
     *
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @param ScopeInterface $scope
     * @param StructureFactory $structureFactory
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ScopeInterface $scope,
        StructureFactory $structureFactory,
        ValueFactory $valueFactory
    ) {
        $this->scope = $scope;
        $this->configStructureFactory = $structureFactory;
        $this->configValueFactory = $valueFactory;
    }

    /**
     * Processes value using backend model.
     *
     * @param string $scope
     * @param string $scopeCode
     * @param string $value
     * @param string $path
     * @return string
     */
    public function process($scope, $scopeCode, $value, $path)
    {
        $areaScope = $this->scope->getCurrentScope();
        $this->scope->setCurrentScope(Area::AREA_ADMINHTML);
        /** @var Structure $configStructure */
        $configStructure = $this->configStructureFactory->create();
        $this->scope->setCurrentScope($areaScope);

        /** @var Field $field */
        $field = $configStructure->getElement($path);

        /** @var Value $backendModel */
        $backendModel = $field && $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->configValueFactory->create();
        $backendModel->setPath($path);
        $backendModel->setScope($scope);
        $backendModel->setScopeId($scopeCode);
        $backendModel->setValue($value);
        $backendModel->afterLoad();

        return $backendModel->getValue();
    }
}
