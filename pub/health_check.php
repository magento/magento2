<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

register_shutdown_function("fatalErrorHandler");

try {
    require __DIR__ . '/../app/bootstrap.php';
    /** @var \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory */
    $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    $objectManager = $objectManagerFactory->create([]);
    /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
    $deploymentConfig = $objectManager->get(\Magento\Framework\App\DeploymentConfig::class);
    $envConfig = $deploymentConfig->getConfigData();
    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
} catch (\Exception $e) {
    http_response_code(500);
    exit(1);
}

// check mysql connectivity
foreach ($envConfig['db']['connection'] as $connectionData) {
    try {
        new \PDO(
            "mysql:dbname={$connectionData['dbname']};host={$connectionData['host']}",
            $connectionData['username'],
            $connectionData['password']
        );
    } catch (\Exception $e) {
        $logger->error("MySQL connection failed: " . $e->getMessage());
        http_response_code(500);
        exit(1);
    }
}

// check cache storage availability
if (isset($envConfig['cache']['frontend']) && is_array($envConfig['cache']['frontend'])) {
    foreach ($envConfig['cache']['frontend'] as $cacheConfig) {
        if (!isset($cacheConfig['backend']) || !isset($cacheConfig['backend_options'])) {
            $logger->error("Cache configuration is invalid");
            http_response_code(500);
            exit(1);
        }
        $cacheBackendClass = $cacheConfig['backend'];
        try {
            /** @var \Zend_Cache_Backend_Interface $backend */
            $backend = new $cacheBackendClass($cacheConfig['backend_options']);
            $backend->test('test_cache_id');
        } catch (\Exception $e) {
            $logger->error("Cache storage is not accessible");
            http_response_code(500);
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
    if ($error !== NULL) {
        http_response_code(500);
        exit(1);
    }
}
