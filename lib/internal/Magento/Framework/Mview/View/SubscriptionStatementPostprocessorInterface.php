<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

interface SubscriptionStatementPostprocessorInterface
{
    /**
     * Postprocess subscription statement.
     *
     * @param string $tableName
     * @param string $event
     * @param string $statement
     * @return string
     */
    public function process(string $tableName, string $event, string $statement): string;
}
