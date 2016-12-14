<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
    require __DIR__ . '/../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    http_response_code(500);
    exit(1);
}

try {
    /** @var \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory */
    $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
    /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
    $deploymentConfig = $objectManagerFactory->create([])->get(\Magento\Framework\App\DeploymentConfig::class);
    $envConfig = $deploymentConfig->getConfigData();
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
    } catch (\PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        http_response_code(500);
    }
}

// check Redis availability
if (isset($envConfig['cache']['frontend']) && is_array($envConfig['cache']['frontend'])) {
    foreach ($envConfig['cache']['frontend'] as $cacheConfig) {
        if (!isset($cacheConfig['backend']) || !isset($cacheConfig['backend_options'])) {
            http_response_code(500);
        }
        $cacheBackendClass = $cacheConfig['backend'];
        try {
            /** @var \Zend_Cache_Backend_ExtendedInterface $backend */
            $backend = new $cacheBackendClass($cacheConfig['backend_options']);
            $backend->test('test_cache_id');
        } catch (Exception $e) {
            http_response_code(500);
        }
    }
}
