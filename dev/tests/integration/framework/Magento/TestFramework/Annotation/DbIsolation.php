<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

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
     * @var string[]
     */
    private $dbStateTables = [
        'catalog_product_entity' => 'assertIsEmpty',
        'eav_attribute' => 'eavAttributeAssert',
        'catalog_category_entity' => 'assertTwoRecords',
        'eav_attribute_set' => 'attributeSetAssert',
        'store' => 'assertTwoRecords'
    ];

    /**
     * Assert number of attribute sets
     *
     * @param array $data
     * @return array
     */
    private function attributeSetAssert(array $data): array
    {
        if (count($data) > 9) {
            return array_slice($data, 9, count($data) - 9);
        }

        return [];
    }

    /**
     * Assert that array has only 2 records
     *
     * @param array $data
     * @return array
     */
    private function assertTwoRecords(array $data): array
    {
        //2 default records
        if (count($data) > 2) {
            return array_slice($data, 2, count($data) - 2);
        }

        return [];
    }

    /**
     * Assert that EAV attributes are only 178
     *
     * @param array $data
     * @return array
     */
    private function eavAttributeAssert(array $data): array
    {
        //178 - default number of attributes
        if (count($data) > 178) {
            return array_slice($data, 178, count($data) - 178);
        }

        return [];
    }

    /**
     * Assert array is empty
     *
     * @param $data
     */
    private function assertIsEmpty(array $data): array
    {
        if (!empty($data)) {
            return $data;
        }

        return [];
    }

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
            foreach ($this->dbStateTables as $dbStateTable => $method) {
                $data = $this->pullDbState($dbStateTable);
                $data = $this->{$method}($data);

                if ($data) {
                    $isolationProblem[$dbStateTable] = $data;
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
