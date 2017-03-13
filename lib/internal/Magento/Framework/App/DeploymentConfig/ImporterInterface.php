<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Interface for importers which import data from shared configuration files to appropriate data storage.
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
}
