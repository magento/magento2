<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\DataGenerator\Handlers\DataObjectHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Handlers\PersistedObjectHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Persist\CurlHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Objects\EntityDataObject;
use Magento\FunctionalTestingFramework\DataGenerator\Persist\DataPersistenceHandler;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\ObjectManagerFactory;

/**
 * This helper use to create/delete products:
 *
 * - CurlHandler::executeRequest() for all API calls
 * - Operation definitions from ProductMeta.xml
 * - EntityDataObject for request structure
 *
 * NO custom HTTP clients, NO external dependencies, NO custom helpers
 */
class ProductApiHelper extends Helper
{
    /**
     * Create products using the ACTUAL category entity object as dependent object.
     *
     * @param string $categoryStepKey
     * @param int $count
     * @param int $quantity
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    public function createProduct(
        string $categoryStepKey,
        int $count = 1,
        int $quantity = 100,
        string $entityType = 'ApiSimpleProduct'
    ): array {
        $createdSkus = [];
        $timestamp = time();

        for ($i = 1; $i <= $count; $i++) {
            try {
                // Get the predefined product entity from MFTF (now dynamic!)
                $apiProductEntity = DataObjectHandler::getInstance()->getObject($entityType);

                if ($apiProductEntity === null) {
                    continue; // Skip this iteration if entity not found
                }

                // Create unique SKU and name for this product
                $uniqueSku =  strtolower(str_replace('Api', '', $entityType)). '-' . $timestamp . '_' . $i;
                $uniqueName = str_replace('Api', '', $entityType)  . $timestamp . '_' . $i;

                // Override fields - this is how MFTF handles custom values
                $overrideFields = [
                    'sku' => $uniqueSku,
                    'name' => $uniqueName,
                    'quantity' => $quantity  // Override the default quantity (100) with our value
                ];

                // Use actual entity object as dependent object!
                $dependentObjects = [];
                $categoryEntityObject = $this->getCategoryEntity($categoryStepKey);
                if ($categoryEntityObject !== null) {
                    $dependentObjects[] = $categoryEntityObject;
                }

                // Use MFTF's native DataPersistenceHandler with ACTUAL entity object
                $persistenceHandler = new DataPersistenceHandler(
                    $apiProductEntity,
                    $dependentObjects, // Pass the ACTUAL category entity object
                    $overrideFields
                );

                // Create the entity using MFTF's native mechanism
                $persistenceHandler->createEntity();

                // Get the created object to extract the SKU
                $createdObject = $persistenceHandler->getCreatedObject();
                if ($createdObject && isset($createdObject->getAllData()['sku'])) {
                    $createdSkus[] = $createdObject->getAllData()['sku'];
                } else {
                    $createdSkus[] = $uniqueSku; // Fallback to our generated SKU
                }
                usleep(100000);
            } catch (\Exception $e) {
                // Log error and continue with fallback SKU
                error_log("Product creation failed : " . $e->getMessage());
            }
        }
        return $createdSkus;
    }

    /**
     * Create products using MFTF's native product entities with ACTUAL category entity object.
     *
     * @param string $categoryStepKey
     * @return DataPersistenceHandler|null
     */
    private function getCategoryEntity(string $categoryStepKey): DataPersistenceHandler
    {
        $actualCategoryEntityObject = null;

        // Try to get the actual category entity object using reflection
        if ($categoryStepKey) {
            try {
                $persistedHandler = PersistedObjectHandler::getInstance();

                // Use reflection to access the private retrieveEntity method
                $reflection = new \ReflectionClass($persistedHandler);
                $retrieveEntityMethod = $reflection->getMethod('retrieveEntity');

                // Call the private method to get the actual DataPersistenceHandler object
                $actualCategoryEntityObject = $retrieveEntityMethod->invoke(
                    $persistedHandler,
                    $categoryStepKey,
                    'test'
                );
            } catch (\Exception $e) {
                // Exception in retrieving category entity object, continue without category
                error_log(
                    "Failed to retrieve category entity while creating product for stepKey '{$categoryStepKey}': " .
                    $e->getMessage()
                );
            }
        }
        return $actualCategoryEntityObject;
    }

    /**
     * Delete all products using ONLY MFTF CurlHandler
     *
     * @param string $enableLogging Enable progress logging (true/false)
     * @param string $pageSize Products per page
     * @return void
     */
    public function deleteAllProductsApi(string $enableLogging = 'true', string $pageSize = '50'): void
    {
        $enableLog = ($enableLogging === 'true');
        $pageNum = (int)$pageSize;
        $stats =  [
            'total_deleted' => 0,
            'total_failed' => 0,
        ];

        $this->logMessage($enableLog, "Starting product deletion...");

        try {
            $allProducts = $this->getAllProducts($pageNum);

            if ($this->handleEmptyProductList($allProducts, $enableLog)) {
                return;
            }

            $this->processProductDeletion($allProducts, $stats, $enableLog);
            $message = "Product deletion completed: {$stats['total_deleted']} successful, " .
                "{$stats['total_failed']} failed.";
            $this->logMessage($enableLog, $message);

        } catch (\Exception $e) {
            $this->logMessage($enableLog, "ERROR: Product deletion failed: " . $e->getMessage());
        }
    }

    /**
     * Delete single product by SKU using MFTF CurlHandler
     *
     * @param string $sku
     * @param array $stats
     * @return void
     * @throws \Exception
     */
    public function deleteBySku(string $sku, array &$stats): void
    {
        if (empty($sku)) {
            throw new \Exception("SKU cannot be empty");
        }

        $encodedSku = urlencode($sku);
        $productEntity =  new EntityDataObject(
            'product_to_delete_' . hash('sha256', $sku),
            'product',
            ['sku' => $encodedSku],
            [],
            [],
            [],
            null,
            null,
            null
        );

        $curlHandler = ObjectManagerFactory::getObjectManager()->create(
            CurlHandler::class,
            [
                'operation' => 'delete',
                'entityObject' => $productEntity,
                'storeCode' => null
            ]
        );
        $response = $curlHandler->executeRequest([]);

        if (!$this->isResponseSuccessful($response)) {
            $errorMessage = "Product deletion failed for '{$sku}' - unexpected response: " . json_encode($response);
            throw new \Exception($errorMessage);
        }

        $stats['total_deleted']++;
    }

    /**
     * Log message if logging is enabled
     *
     * @param bool $enableLog
     * @param string $message
     * @return void
     */
    private function logMessage(bool $enableLog, string $message): void
    {
        if ($enableLog) {
            printf("%s\n", $message);
        }
    }

    /**
     * Handle empty product list scenario
     *
     * @param array $allProducts
     * @param bool $enableLog
     * @return bool True if should return early, false to continue
     */
    private function handleEmptyProductList(array $allProducts, bool $enableLog): bool
    {
        if (empty($allProducts)) {
            $this->logMessage($enableLog, "No products found.");
            return true;
        }
        return false;
    }

    /**
     * Process deletion of all products
     *
     * @param array $allProducts
     * @param array $stats
     * @param bool $enableLog
     * @return void
     */
    private function processProductDeletion(array $allProducts, array &$stats, bool $enableLog): void
    {
        foreach ($allProducts as $product) {
            $sku = $product['sku'] ?? '';

            if (empty($sku)) {
                $stats['total_failed']++;
                continue;
            }

            try {
                $this->deleteBySku($sku, $stats);
            } catch (\Exception $e) {
                $stats['total_failed']++;
                $this->logMessage($enableLog, "Failed to delete '{$sku}': " . $e->getMessage());
            }
        }
    }

    /**
     * Check if API response indicates success
     *
     * @param $response
     * @return bool
     */
    private function isResponseSuccessful($response): bool
    {
        // Direct success values
        if (in_array($response, [true, 'true', '1', ''], true)) {
            return true;
        }

        // String response handling
        if (is_string($response)) {
            return $this->validateStringResponse($response);
        }

        return false;
    }

    /**
     * Validate string response for success indicators
     *
     * @param string $response
     * @return bool
     */
    private function validateStringResponse(string $response): bool
    {
        // Try JSON parsing first
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return ($jsonData === true ||
                ($jsonData['success'] ?? false) ||
                ($jsonData['status'] ?? '') === 'success');
        }

        // Plain text fallback
        return in_array(trim($response), ['true', '1', 'success', ''], true);
    }

    /**
     * Get products using MFTF CurlHandler with GetProductList operation
     *
     * @param int $pageSize Products per page
     * @return array Product data
     * @throws \Exception
     */
    private function getAllProducts(int $pageSize): array
    {
        try {
            // Create EntityDataObject for GetProductList operation to get ALL product types
            $productListEntity = new EntityDataObject(
                'product_list_all_types',
                'product_list',                    // Matches operation dataType
                [
                    'pageSize' => $pageSize,
                    'currentPage' => 1,
                    'fields' => 'items[sku,name,type_id,status]'  // Include type_id to see all types
                ],
                [],
                [],
                [],
                null,
                null,
                null
            );

            // Create CurlHandler for GetProductList operation
            $curlHandler = ObjectManagerFactory::getObjectManager()->create(
                CurlHandler::class,
                [
                    'operation' => 'get',
                    'entityObject' => $productListEntity,
                    'storeCode' => null
                ]
            );

            // Execute using MFTF CurlHandler
            $response = $curlHandler->executeRequest([]);

            // Parse response
            if (is_string($response)) {
                $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $responseData = $response;
            }

            return $responseData['items'] ?? [];

        } catch (\Exception $e) {
            $errorMessage = "Failed to retrieve products: " . $e->getMessage();
            throw new \Exception($errorMessage, 0, $e);
        }
    }
}
