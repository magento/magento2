<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Interface for importers which import data from shared configuration files to appropriate data storage.
 *
 * @api
 */
interface ImporterInterface
{
    /**
     * Imports data from shared configuration files to appropriate data storage.
     *
     * @param array $data Data that should be imported
     * @return string[] The array of messages that generated during importing
     * @throws InvalidTransitionException In case of errors during importing (e.g., cannot save some data).
     * All changed during importing data is rolled back
     */
    public function import(array $data);

    /**
     * Returns array of warning messages that describes what changes could happen during the import.
     *
     * @param array $data Data that should be imported
     * @return string[] The array of warning messages
     */
    public function getWarningMessages(array $data);
}
