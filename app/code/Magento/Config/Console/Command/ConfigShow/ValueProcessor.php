<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigShow;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Class processes values using backend model which declared in system.xml.
 *
 * @api
 * @since 101.0.0
 */
class ValueProcessor
{
    /**
     * Placeholder for the output of sensitive data.
     */
    public const SAFE_PLACEHOLDER = '******';

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
     * Object for managing configuration scope.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * The json serializer.
     *
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /** @var Structure */
    private $configStructure;

    /**
     * @param ScopeInterface $scope The object for managing configuration scope
     * @param StructureFactory $structureFactory The system configuration structure factory.
     * @param ValueFactory $valueFactory The factory of object that
     *        implement \Magento\Framework\App\Config\ValueInterface
     * @param JsonSerializer $jsonSerializer The json serializer
     */
    public function __construct(
        ScopeInterface $scope,
        StructureFactory $structureFactory,
        ValueFactory $valueFactory,
        JsonSerializer $jsonSerializer
    ) {
        $this->scope = $scope;
        $this->configStructureFactory = $structureFactory;
        $this->configValueFactory = $valueFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Processes value to display using backend model.
     *
     * @param string $scope The scope of configuration. E.g. 'default', 'website' or 'store'
     * @param string $scopeCode The scope code of configuration
     * @param string $value The value to process
     * @param string $path The configuration path for getting backend model. E.g. scope_id/group_id/field_id
     * @return string processed value result
     * @since 101.0.0
     */
    public function process($scope, $scopeCode, $value, $path)
    {
        $configStructure = $this->getConfigStructure();

        /** @var Field $field */
        $field = $configStructure->getElementByConfigPath($path);

        /** @var Value $backendModel */
        $backendModel = $field instanceof Field && $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->configValueFactory->create();

        if ($backendModel instanceof Encrypted) {
            return  $value ? self::SAFE_PLACEHOLDER : null;
        }

        $backendModel->setPath($path);
        $backendModel->setScope($scope);
        $backendModel->setScopeId($scopeCode);
        $backendModel->setValue($value);
        $backendModel->afterLoad();
        $processedValue = $backendModel->getValue();

        /**
         * If $processedValue is array it means that $value is json array (string).
         * It should be converted to string for displaying.
         */
        return is_array($processedValue) ? $this->jsonSerializer->serialize($processedValue) : $processedValue;
    }

    /**
     * Retrieve config structure
     *
     * @return Structure
     */
    private function getConfigStructure(): Structure
    {
        if (empty($this->configStructure)) {
            $areaScope = $this->scope->getCurrentScope();
            $this->scope->setCurrentScope(Area::AREA_ADMINHTML);
            /** @var Structure $configStructure */
            $this->configStructure = $this->configStructureFactory->create();
            $this->scope->setCurrentScope($areaScope);
        }
        return $this->configStructure;
    }
}
