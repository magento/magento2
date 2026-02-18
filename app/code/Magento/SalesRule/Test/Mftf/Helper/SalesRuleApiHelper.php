<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\DataGenerator\Persist\CurlHandler;
use Magento\FunctionalTestingFramework\DataGenerator\Objects\EntityDataObject;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\ObjectManagerFactory;
use Magento\FunctionalTestingFramework\Util\Logger\LoggingUtil;

/**
 * This helper use to delete sales rules via API:
 *
 * - CurlHandler::executeRequest() for all API calls
 * - Operation definitions from SalesRuleMeta.xml
 * - EntityDataObject for request structure
 *
 * NO custom HTTP clients, NO external dependencies, NO custom helpers
 */
class SalesRuleApiHelper extends Helper
{
    /**
     * Delete all cart price rules using API (faster and more reliable than UI-based deletion)
     *
     * @param string $enableLogging Enable progress logging (true/false)
     * @param string $pageSize Rules per page
     * @return void
     */
    public function deleteAllSalesRulesApi(string $enableLogging = 'true', string $pageSize = '100'): void
    {
        $enableLog = ($enableLogging === 'true');
        $pageNum = (int)$pageSize;
        $stats = [
            'total_deleted' => 0,
            'total_failed' => 0,
        ];

        $this->logMessage($enableLog, "Starting cart price rule deletion via API...");

        try {
            $allRules = $this->getAllSalesRules($pageNum);

            if ($this->handleEmptyRuleList($allRules, $enableLog)) {
                return;
            }

            $this->processRuleDeletion($allRules, $stats, $enableLog);
            $message = "Cart price rule deletion completed: {$stats['total_deleted']} successful, " .
                "{$stats['total_failed']} failed.";
            $this->logMessage($enableLog, $message);

        } catch (\Exception $e) {
            $this->logMessage($enableLog, "ERROR: Cart price rule deletion failed: " . $e->getMessage());
        }
    }

    /**
     * Delete single rule by ID using MFTF CurlHandler
     *
     * @param int $ruleId
     * @param array $stats
     * @return void
     * @throws \Exception
     */
    private function deleteByRuleId(int $ruleId, array &$stats): void
    {
        if (empty($ruleId)) {
            throw new \Exception("Rule ID cannot be empty");
        }

        $ruleEntity = new EntityDataObject(
            name: 'salesrule_to_delete_' . $ruleId,
            type: 'SalesRule',
            data: ['rule_id' => $ruleId],
            linkedEntities: [],
            uniquenessData: [],
            vars: [],
            parentEntity: null,
            filename: null,
            deprecated: null
        );

        $curlHandler = ObjectManagerFactory::getObjectManager()->create(
            CurlHandler::class,
            [
                'operation' => 'delete',
                'entityObject' => $ruleEntity,
                'storeCode' => null
            ]
        );
        $response = $curlHandler->executeRequest([]);

        if (!$this->isResponseSuccessful($response)) {
            $errorMessage = "Rule deletion failed for ID '{$ruleId}' - unexpected response: " . json_encode($response);
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
            LoggingUtil::getInstance()->getLogger(self::class)->info($message);
        }
    }

    /**
     * Handle empty rule list scenario
     *
     * @param array $allRules
     * @param bool $enableLog
     * @return bool True if should return early, false to continue
     */
    private function handleEmptyRuleList(array $allRules, bool $enableLog): bool
    {
        if (empty($allRules)) {
            $this->logMessage($enableLog, "No cart price rules found.");
            return true;
        }
        return false;
    }

    /**
     * Process deletion of all rules
     *
     * @param array $allRules
     * @param array $stats
     * @param bool $enableLog
     * @return void
     */
    private function processRuleDeletion(array $allRules, array &$stats, bool $enableLog): void
    {
        foreach ($allRules as $rule) {
            $ruleId = $rule['rule_id'] ?? '';

            if (empty($ruleId)) {
                $stats['total_failed']++;
                continue;
            }

            try {
                $this->deleteByRuleId((int)$ruleId, $stats);
            } catch (\Exception $e) {
                $stats['total_failed']++;
                $ruleName = $rule['name'] ?? 'Unknown';
                $errorMsg = "Failed to delete rule '{$ruleName}' (ID: {$ruleId}): " . $e->getMessage();
                $this->logMessage($enableLog, $errorMsg);
            }
        }
    }

    /**
     * Check if API response indicates success
     *
     * @param mixed $response
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
     * Get all cart price rules using MFTF CurlHandler with GetSalesRuleList operation
     *
     * @param int $pageSize Rules per page
     * @return array Rule data
     * @throws \Exception
     */
    private function getAllSalesRules(int $pageSize): array
    {
        try {
            // Create EntityDataObject for GetSalesRuleList operation
            $ruleListEntity = new EntityDataObject(
                name: 'salesrule_list_all',
                type: 'salesrule_list',
                data: [
                    'pageSize' => $pageSize,
                    'currentPage' => 1,
                    'fields' => 'items[rule_id,name,is_active]'
                ],
                linkedEntities: [],
                uniquenessData: [],
                vars: [],
                parentEntity: null,
                filename: null,
                deprecated: null
            );

            // Create CurlHandler for GetSalesRuleList operation
            $curlHandler = ObjectManagerFactory::getObjectManager()->create(
                CurlHandler::class,
                [
                    'operation' => 'get',
                    'entityObject' => $ruleListEntity,
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
            $errorMessage = "Failed to retrieve cart price rules: " . $e->getMessage();
            throw new \Exception($errorMessage, 0, $e);
        }
    }
}
