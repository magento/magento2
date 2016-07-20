<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;
use Magento\Framework\Phrase;

/**
 * Publisher config data validator. Validates that publisher has only one enabled connection at the same time
 */
class ActiveConnection implements ValidatorInterface
{
    /**
     * Validate merged publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     */
    public function validate($configData)
    {
        $errors = [];
        foreach ($configData as $name => $publisherData) {
            if (!isset($publisherData['connections'])) {
                continue;
            }

            if (!is_array($publisherData['connections'])) {
                $errors[] = sprintf('Invalid connections configuration for publisher %s', $name);
                continue;
            }

            $enabledConnections = 0;
            foreach ($publisherData['connections'] as $connectionConfig) {
                if (!isset($connectionConfig['disabled']) || $connectionConfig['disabled'] == false) {
                    $enabledConnections++;
                }
            }

            if ($enabledConnections > 1) {
                $errors[] = sprintf('More than 1 enabled connections configured for publisher %s. ', $name);
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(PHP_EOL, $errors));
        }
    }
}
