<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;

/**
 * Emulator adminhtml area for CLI command.
 */
class EmulatedAdminhtmlAreaProcessor
{
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
     */
    public function __construct(ScopeInterface $scope, State $state)
    {
        $this->scope = $scope;
        $this->state = $state;
    }

    /**
     * Emulate callback inside adminhtml area code.
     *
     * Returns the return value of the callback.
     *
     * @param callable $callback The callable to be called
     * @param array $params The parameters to be passed to the callback, as an indexed array
     * @return mixed
     * @throws \Exception
     */
    public function process(callable $callback, array $params = [])
    {
        $currentScope = $this->scope->getCurrentScope();

        try {
            return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($callback, $params) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);
                return call_user_func_array($callback, $params);
            });
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }
    }
}
