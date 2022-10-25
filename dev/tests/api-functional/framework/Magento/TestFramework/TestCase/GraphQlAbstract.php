<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test case for Web API functional tests for Graphql.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class GraphQlAbstract extends WebapiAbstract
{
    /**
     * The instantiated GraphQL client.
     *
     * @var \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private $graphQlClient;

    /**
     * @var \Magento\Framework\App\Cache
     */
    private $appCache;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $envConfigPath;

    /**
     * @var Reader
     */
    private $envConfigReader;

    /**
     * @var PhpFormatter
     */
    private $formatter;

    /**
     * Perform GraphQL query call via GET to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array|int|string|float|bool GraphQL call results
     * @throws \Exception
     */
    public function graphQlQuery(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ) {
        return $this->getGraphQlClient()->get(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Perform GraphQL mutations call via POST to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array|int|string|float|bool GraphQL call results
     * @throws \Exception
     */
    public function graphQlMutation(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ) {
        return $this->getGraphQlClient()->post(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Perform GraphQL query via GET and returns only the response headers
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array
     */
    public function graphQlQueryWithResponseHeaders(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ): array {
        return $this->getGraphQlClient()->getWithResponseHeaders(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Perform GraphQL query via POST and returns the response headers
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array
     */
    public function graphQlMutationWithResponseHeaders(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ): array {
        return $this->getGraphQlClient()->postWithResponseHeaders(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Compose headers
     *
     * @param array $headers
     * @return string[]
     */
    private function composeHeaders(array $headers): array
    {
        $headersArray = [];
        foreach ($headers as $key => $value) {
            $headersArray[] = sprintf('%s: %s', $key, $value);
        }
        return $headersArray;
    }

    /**
     * Clear cache so integration test can alter cached GraphQL schema
     *
     * @return bool
     */
    protected function cleanCache()
    {
        return $this->getAppCache()->clean(\Magento\Framework\App\Config::CACHE_TAG);
    }

    /**
     * Return app cache setup.
     *
     * @return \Magento\Framework\App\Cache
     */
    private function getAppCache()
    {
        if (null === $this->appCache) {
            $this->appCache = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache::class);
        }
        return $this->appCache;
    }

    /**
     * Get GraphQL adapter (create if requested one does not exist).
     *
     * @return \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private function getGraphQlClient()
    {
        if ($this->graphQlClient === null) {
            $this->graphQlClient = Bootstrap::getObjectManager()->get(
                \Magento\TestFramework\TestCase\GraphQl\Client::class
            );
        }
        return $this->graphQlClient;
    }

    /**
     * Compare actual response fields with expected
     *
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    protected function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            self::assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            self::assertArrayHasKey(
                $responseField,
                $actualResponse,
                "Response array does not contain key '{$responseField}'"
            );
            self::assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }

    /**
     * If the cache id salt didn't exist in env.php before a GraphQL request it gets added. To prevent test failures
     * due to a config getting changed (which is normally illegal), the salt needs to be removed from env.php after
     * a test if it wasn't there before.
     *
     * @see \Magento\TestFramework\Isolation\DeploymentConfig
     *
     * @inheritdoc
     */
    protected function runTest()
    {
        /** @var Reader $reader */
        if (!$this->envConfigPath) {
            /** @var ConfigFilePool $configFilePool */
            $configFilePool = Bootstrap::getObjectManager()->get(ConfigFilePool::class);
            $this->envConfigPath = $configFilePool->getPath(ConfigFilePool::APP_ENV);
        }
        $this->envConfigReader = $this->envConfigReader ?: Bootstrap::getObjectManager()->get(Reader::class);
        $initialConfig = $this->envConfigReader->load(ConfigFilePool::APP_ENV);

        try {
            return parent::runTest();
        } finally {
            $this->formatter = $this->formatter ?: new PhpFormatter();
            $this->filesystem = $this->filesystem ?: Bootstrap::getObjectManager()->get(Filesystem::class);
            $cacheSaltPathChunks = explode('/', CacheIdCalculator::SALT_CONFIG_PATH);
            $currentConfig = $this->envConfigReader->load(ConfigFilePool::APP_ENV);
            $resetConfig = $this->resetAddedSection($initialConfig, $currentConfig, $cacheSaltPathChunks);
            $resetFileContents = $this->formatter->format($resetConfig);
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
            $directoryWrite->writeFile($this->envConfigPath, $resetFileContents);
        }
    }

    /**
     * Go over the current deployment config and unset a section that was not present in the pre-test deployment config
     *
     * @param array $initial
     * @param array $current
     * @param string[] $chunks
     * @return array
     */
    private function resetAddedSection(array $initial, array $current, array $chunks): array
    {
        if ($chunks) {
            $chunk = array_shift($chunks);
            if (!isset($initial[$chunk])) {
                if (isset($current[$chunk])) {
                    unset($current[$chunk]);
                }
            } elseif (isset($current[$chunk]) && is_array($initial[$chunk]) && is_array($current[$chunk])) {
                $current[$chunk] = $this->resetAddedSection($initial[$chunk], $current[$chunk], $chunks);
            }
        }
        return $current;
    }
}
