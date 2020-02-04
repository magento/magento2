<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable Squiz.Functions.GlobalFunction
 */
use Magento\Framework\Config\ConfigOptionsListConstants;

// phpcs:ignore Magento2.Functions.DiscouragedFunction
register_shutdown_function("fatalErrorHandler");

try {
    // phpcs:ignore Magento2.Security.IncludeFile
    require __DIR__ . '/../app/bootstrap.php';
    /** @var \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory */
    $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    $objectManager = $objectManagerFactory->create([]);
    /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
    $deploymentConfig = $objectManager->get(\Magento\Framework\App\DeploymentConfig::class);
    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
} catch (\Exception $e) {
    http_response_code(500);
    // phpcs:ignore Magento2.Security.LanguageConstruct
    exit(1);
}

// check mysql connectivity
foreach ($deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS) as $connectionData) {
    try {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $dbAdapter */
        $dbAdapter = $objectManager->create(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['config' => $connectionData]
        );
        $dbAdapter->getConnection();
    } catch (\Exception $e) {
        http_response_code(500);
        $logger->error("MySQL connection failed: " . $e->getMessage());
        // phpcs:ignore Magento2.Security.LanguageConstruct
        exit(1);
    }
}

// check cache storage availability
$cacheConfigs = $deploymentConfig->get(ConfigOptionsListConstants::KEY_CACHE_FRONTEND);
if ($cacheConfigs) {
    foreach ($cacheConfigs as $cacheConfig) {
        // allow config if only available "id_prefix"
        if (count($cacheConfig) === 1 && isset($cacheConfig['id_prefix'])) {
            continue;
        } elseif (!isset($cacheConfig[ConfigOptionsListConstants::CONFIG_PATH_BACKEND]) ||
            !isset($cacheConfig[ConfigOptionsListConstants::CONFIG_PATH_BACKEND_OPTIONS])) {
            http_response_code(500);
            $logger->error("Cache configuration is invalid");
            // phpcs:ignore Magento2.Security.LanguageConstruct
            exit(1);
        }
        $cacheBackendClass = $cacheConfig[ConfigOptionsListConstants::CONFIG_PATH_BACKEND];
        try {
            /** @var \Zend_Cache_Backend_Interface $backend */
            $backend = new $cacheBackendClass($cacheConfig[ConfigOptionsListConstants::CONFIG_PATH_BACKEND_OPTIONS]);
            $backend->test('test_cache_id');
        } catch (\Exception $e) {
            http_response_code(500);
            $logger->error("Cache storage is not accessible");
            // phpcs:ignore Magento2.Security.LanguageConstruct
            exit(1);
        }
    }
}

/**
 * Handle any fatal errors
 *
 * @return void
 */
function fatalErrorHandler()
{
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        http_response_code(500);
    }
}
