<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Validator;

use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;

/**
 * Publisher config data validator. Validates that publisher has only one enabled connection at the same time
 */
class EnabledConnection implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        $errors = [];
        foreach ($configData as $name => $publisherData) {
            if (!isset($publisherData['connections'])) {
                continue;
            }
            $enabledConnections = 0;
            foreach ($publisherData['connections'] as $connectionConfig) {
                if ($connectionConfig['disabled'] == false) {
                    $enabledConnections++;
                }
            }

            if ($enabledConnections > 1) {
                $errors[] = sprintf('More than 1 enabled connections configured for publisher %s.', $name);
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(' ', $errors));
        }
    }
}
