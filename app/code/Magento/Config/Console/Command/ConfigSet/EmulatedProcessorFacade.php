<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Processor facade for config:set command with emulated adminhtml area.
 */
class EmulatedProcessorFacade
{
    /**
     * The factory for processor facade.
     *
     * @var ProcessorFacadeFactory
     */
    private $processorFacadeFactory;

    /**
     * The application scope manager.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * The application state manager.
     *
     * @var State
     */
    private $state;

    /**
     * @param ScopeInterface $scope The application scope manager
     * @param State $state The application state manager
     * @param ProcessorFacadeFactory $processorFacadeFactory The factory for processor facade
     */
    public function __construct(
        ScopeInterface $scope,
        State $state,
        ProcessorFacadeFactory $processorFacadeFactory
    ) {
        $this->scope = $scope;
        $this->state = $state;
        $this->processorFacadeFactory = $processorFacadeFactory;
    }

    /**
     * Processes config:set command.
     *
     * @param string $path The configuration path in format group/section/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @param boolean $lock The lock flag
     * @return string Processor response message
     * @throws RuntimeException If exception was catch
     */
    public function process($path, $value, $scope, $scopeCode, $lock)
    {
        $currentScope = $this->scope->getCurrentScope();

        try {
            // Emulating adminhtml scope to be able to read configs.
            return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use (
                $path,
                $value,
                $scope,
                $scopeCode,
                $lock
            ) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

                return $this->processorFacadeFactory->create()->process(
                    $path,
                    $value,
                    $scope,
                    $scopeCode,
                    $lock
                );
            });
        } catch (LocalizedException $exception) {
            throw new RuntimeException(__('%1', $exception->getMessage()), $exception);
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }
    }
}
