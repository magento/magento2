<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Annotation;

/**
 * Implementation of the @magentoDbIsolation DocBlock annotation
 */
class DbIsolation
{
    const MAGENTO_DB_ISOLATION = 'magentoDbIsolation';
    /**
     * @var bool
     */
    protected $_isIsolationActive = false;

    /**
     * Handler for 'startTestTransactionRequest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function startTestTransactionRequest(
        \PHPUnit_Framework_TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        $methodIsolation = $this->_getIsolation($test);
        if ($this->_isIsolationActive) {
            if ($methodIsolation === false) {
                $param->requestTransactionRollback();
            }
        } elseif ($methodIsolation || ($methodIsolation === null && $this->_getIsolation($test))) {
            $param->requestTransactionStart();
        }
    }

    /**
     * Handler for 'endTestTransactionRequest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function endTestTransactionRequest(
        \PHPUnit_Framework_TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        if ($this->_isIsolationActive && $this->_getIsolation($test)) {
            $param->requestTransactionRollback();
        }
    }

    /**
     * Handler for 'startTransaction' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTransaction(\PHPUnit_Framework_TestCase $test)
    {
        $this->_isIsolationActive = true;
    }

    /**
     * Handler for 'rollbackTransaction' event
     */
    public function rollbackTransaction()
    {
        $this->_isIsolationActive = false;
    }

    /**
     * Retrieve database isolation annotation value for the current scope.
     * Possible results:
     *   NULL  - annotation is not defined
     *   TRUE  - annotation is defined as 'enabled'
     *   FALSE - annotation is defined as 'disabled'
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return bool|null Returns NULL, if isolation is not defined for the current scope
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getIsolation(\PHPUnit_Framework_TestCase $test)
    {
        $annotations = $this->getAnnotations($test);
        if (isset($annotations[self::MAGENTO_DB_ISOLATION])) {
            $isolation = $annotations[self::MAGENTO_DB_ISOLATION];
            if ($isolation !== ['enabled'] && $isolation !== ['disabled']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid "@magentoDbIsolation" annotation, can be "enabled" or "disabled" only.')
                );
            }
            return $isolation === ['enabled'];
        }
        return null;
    }

    /**
     * @param \PHPUnit_Framework_TestCase $test
     * @return array
     */
    private function getAnnotations(\PHPUnit_Framework_TestCase $test)
    {
        $annotations = $test->getAnnotations();
        return array_replace($annotations['class'], $annotations['method']);
    }
}
