<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

/**
 * Execute added callbacks for transaction commit.
 */
class ExecuteCommitCallbacks
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute callbacks after commit.
     *
     * @param AdapterInterface $subject
     * @param AdapterInterface $result
     * @return AdapterInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCommit(AdapterInterface $subject, AdapterInterface $result): AdapterInterface
    {
        if ($result->getTransactionLevel() === 0) {
            $callbacks = CallbackPool::get(spl_object_hash($result));
            foreach ($callbacks as $callback) {
                try {
                    call_user_func($callback);
                } catch (\Throwable $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $result;
    }
}
