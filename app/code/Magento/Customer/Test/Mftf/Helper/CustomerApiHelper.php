<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\DataGenerator\Handlers\DataObjectHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Persist\CurlHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Objects\EntityDataObject;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\ObjectManagerFactory;

/**
 * Helper for customer deletion via API with proper exception handling
 */
class CustomerApiHelper extends Helper
{
    /**
     * Delete all customers using API
     *
     * @param string $enableLogging Enable progress logging (default: 'true')
     * @param string $pageSize Customers per page for search (default: '100')
     * @return void
     */
    public function deleteAllCustomersApi(string $enableLogging = 'true', string $pageSize = '100'): void
    {
        $enableLog = ($enableLogging === 'true');
        $pageNum = (int)$pageSize;
        $stats = [
            'total_deleted' => 0,
            'total_failed' => 0,
            'failed_reasons' => []
        ];

        $this->logMessage($enableLog, "=== Starting customer deletion via API ===");

        try {
            $allCustomers = $this->getAllCustomers($pageNum);

            if ($this->handleEmptyCustomerList($allCustomers, $enableLog)) {
                return;
            }

            $this->logMessage($enableLog, "Found " . count($allCustomers) . " customers to delete");
            $this->processCustomerDeletion($allCustomers, $stats, $enableLog);

            $message = "Customer deletion completed: {$stats['total_deleted']} successful, " .
                "{$stats['total_failed']} failed.";
            $this->logMessage($enableLog, $message);

            if (!empty($stats['failed_reasons'])) {
                $reasons = array_count_values($stats['failed_reasons']);
                $this->logMessage($enableLog, "Failure breakdown:");
                foreach ($reasons as $reason => $count) {
                    $this->logMessage($enableLog, "  - {$reason}: {$count} customer(s)");
                }
            }

        } catch (\Exception $e) {
            $this->logMessage($enableLog, "ERROR: Customer deletion failed: " . $e->getMessage());
        }

        $this->logMessage($enableLog, "=== Customer deletion complete ===");
    }

    /**
     * Delete single customer by ID
     *
     * @param int $customerId Customer entity ID
     * @param array $stats Statistics array to update
     * @return void
     * @throws \Exception If deletion fails
     */
    private function deleteById(int $customerId, array &$stats): void
    {
        if (empty($customerId)) {
            throw new \Exception("Customer ID cannot be empty");
        }

        $customerEntity = new EntityDataObject(
            'customer_to_delete_' . $customerId,
            'customer',
            ['id' => $customerId],
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
                'entityObject' => $customerEntity,
                'storeCode' => null
            ]
        );

        $response = $curlHandler->executeRequest([]);

        if (!$this->isResponseSuccessful($response)) {
            $errorMessage = "Customer deletion failed for ID '{$customerId}' - " .
                "Response: " . json_encode($response);
            throw new \Exception($errorMessage);
        }

        $stats['total_deleted']++;
    }

    /**
     * Get all customers using search API
     *
     * @param int $pageSize Number of customers per page
     * @return array Customer data array
     * @throws \Exception If retrieval fails
     */
    private function getAllCustomers(int $pageSize): array
    {
        try {
            $customerListEntity = new EntityDataObject(
                'customer_list_all',
                'customer_list',
                [
                    'pageSize' => $pageSize,
                    'currentPage' => 1
                ],
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
                    'operation' => 'get',
                    'entityObject' => $customerListEntity,
                    'storeCode' => null
                ]
            );

            $response = $curlHandler->executeRequest([]);

            if (is_string($response)) {
                $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $responseData = $response;
            }

            return $responseData['items'] ?? [];

        } catch (\Exception $e) {
            throw new \Exception("Failed to retrieve customers: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle empty customer list scenario
     *
     * @param array $allCustomers Customer list
     * @param bool $enableLog Whether to log messages
     * @return bool True if should return early, false to continue
     */
    private function handleEmptyCustomerList(array $allCustomers, bool $enableLog): bool
    {
        if (empty($allCustomers)) {
            $this->logMessage($enableLog, "No customers found to delete.");
            return true;
        }
        return false;
    }

    /**
     * Process deletion of all customers
     *
     * @param array $allCustomers List of customers to delete
     * @param array $stats Statistics array to update
     * @param bool $enableLog Whether to log messages
     * @return void
     */
    private function processCustomerDeletion(array $allCustomers, array &$stats, bool $enableLog): void
    {
        foreach ($allCustomers as $customer) {
            $customerId = $customer['id'] ?? 0;
            $email = $customer['email'] ?? 'unknown';
            $firstname = $customer['firstname'] ?? '';
            $lastname = $customer['lastname'] ?? '';
            $displayName = trim($firstname . ' ' . $lastname) ?: $email;

            if (empty($customerId)) {
                $stats['total_failed']++;
                $this->logMessage($enableLog, "  ✗ Skipped customer (no ID): {$displayName}");
                continue;
            }

            try {
                $this->deleteById((int)$customerId, $stats);
                $this->logMessage($enableLog, "  ✓ Deleted: {$displayName} ({$email})");
            } catch (\Exception $e) {
                $stats['total_failed']++;
                $reason = $this->extractFailureReason($e->getMessage());
                $stats['failed_reasons'][] = $reason;
                $this->logMessage(
                    $enableLog,
                    "  ✗ Failed: {$displayName} ({$email}) - Reason: {$reason}"
                );
            }
        }
    }

    /**
     * Extract failure reason from exception message
     *
     * @param string $errorMessage Exception message
     * @return string Human-readable failure reason
     */
    private function extractFailureReason(string $errorMessage): string
    {
        $errorLower = strtolower($errorMessage);

        if (strpos($errorLower, 'company admin') !== false ||
            strpos($errorLower, 'super_user') !== false ||
            strpos($errorLower, 'cannot delete') !== false) {
            return 'Company Admin (delete company first)';
        }

        if (strpos($errorLower, 'foreign key') !== false ||
            strpos($errorLower, 'integrity constraint') !== false) {
            return 'Foreign Key Constraint';
        }

        if (strpos($errorLower, 'not found') !== false ||
            strpos($errorLower, 'no such entity') !== false) {
            return 'Customer Not Found';
        }

        return 'API Error';
    }

    /**
     * Check if API response indicates success
     *
     * @param mixed $response API response
     * @return bool
     */
    private function isResponseSuccessful($response): bool
    {
        if (in_array($response, [true, 'true', '1', ''], true)) {
            return true;
        }

        if (is_string($response)) {
            return $this->validateStringResponse($response);
        }

        return false;
    }

    /**
     * Validate string response for success indicators
     *
     * @param string $response Response string
     * @return bool
     */
    private function validateStringResponse(string $response): bool
    {
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return ($jsonData === true ||
                ($jsonData['success'] ?? false) ||
                ($jsonData['status'] ?? '') === 'success');
        }

        return in_array(trim($response), ['true', '1', 'success', ''], true);
    }

    /**
     * Log message if logging is enabled
     *
     * @param bool $enableLog Whether to log
     * @param string $message Message to log
     * @return void
     */
    private function logMessage(bool $enableLog, string $message): void
    {
        if ($enableLog) {
            printf("%s\n", $message);
        }
    }
}
