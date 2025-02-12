<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;

/**
 * Emulates callback inside adminhtml area code and adminhtml scope.
 * It is used for CLI commands which should work with data available only in adminhtml scope.
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
     * Emulates callback inside adminhtml area code and adminhtml scope.
     *
     * Returns the return value of the callback.
     *
     * @param callable $callback The callable to be called
     * @param array $params The parameters to be passed to the callback, as an indexed array
     * @return bool|int|float|string|array|null - as the result of this method is the result of callback,
     * you can use callback only with specified in this method return types
     * @throws Exception The exception is thrown if the parameter $callback throws an exception
     */
    public function process(callable $callback, array $params = [])
    {
        $currentScope = $this->scope->getCurrentScope();
        try {
            return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($callback, $params) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                return call_user_func_array($callback, array_values($params));
            });
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }
    }
}
