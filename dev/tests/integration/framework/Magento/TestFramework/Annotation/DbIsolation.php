<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

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
     * This variable was created to keep initial data cached
     *
     * @var array
     */
    private static $isolationCache = [];

    /**
     * @var string[]
     */
    private static $dbStateTables = [
        'catalog_product_entity',
        'eav_attribute',
        'catalog_category_entity',
        'eav_attribute_set',
        'store',
        'store_website',
        'url_rewrite'
    ];

    /**
     * Pull data from specific table
     *
     * @param string $table
     * @return array
     */
    private function pullDbState(string $table): array
    {
        $resource = ObjectManager::getInstance()->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from($table);
        return $connection->fetchAll($select);
    }

    /**
     * Handler for 'startTestTransactionRequest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function startTestTransactionRequest(
        \PHPUnit\Framework\TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        $this->warmUpIsolationCache();
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
     * At the first run before test we need to warm up attributes list to have native attributes list
     *
     * @return void
     */
    private function warmUpIsolationCache(): void
    {
        if (empty(self::$isolationCache)) {
            foreach (self::$dbStateTables as $table) {
                self::$isolationCache[$table] = $this->pullDbState($table);
            }
        }
    }

    /**
     * Compare data difference for m-dimensional array
     *
     * @param array $dataBefore
     * @param array $dataAfter
     * @return bool
     */
    private function dataDiff(array $dataBefore, array $dataAfter): array
    {
        $diff = [];
        if (count($dataBefore) !== count($dataAfter)) {
            $diff = array_slice($dataAfter, count($dataBefore));
        }

        return $diff;
    }

    /**
     * Handler for 'endTestTransactionRequest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @param \Magento\TestFramework\Event\Param\Transaction $param
     */
    public function endTestTransactionRequest(
        \PHPUnit\Framework\TestCase $test,
        \Magento\TestFramework\Event\Param\Transaction $param
    ) {
        if ($this->_isIsolationActive && $this->_getIsolation($test)) {
            $param->requestTransactionRollback();
        } else {
            $isolationProblem = [];
            foreach (self::$dbStateTables as $table) {
                $diff = $this->dataDiff(
                    self::$isolationCache[$table],
                    $this->pullDbState($table)
                );

                if (!empty($diff)) {
                    $isolationProblem[$table] = $diff;
                }
            }

            if (!empty($isolationProblem)) {
                $test::fail("There was a problem with isolation: " . var_export($isolationProblem, true));
            }
        }
    }

    /**
     * Handler for 'startTransaction' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTransaction(\PHPUnit\Framework\TestCase $test)
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
     * @param \PHPUnit\Framework\TestCase $test
     * @return bool|null Returns NULL, if isolation is not defined for the current scope
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getIsolation(\PHPUnit\Framework\TestCase $test)
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
     * @param \PHPUnit\Framework\TestCase $test
     * @return array
     */
    private function getAnnotations(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        return array_replace($annotations['class'], $annotations['method']);
    }
}
