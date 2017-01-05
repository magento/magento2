<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Validator;

use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;

/**
 * Publisher config data validator. Validates that publisher config has all required fields.
 */
class Format implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        $requiredPublisherFields = ['topic', 'disabled', 'connections'];
        $requiredConnectionFields = ['name', 'disabled', 'exchange'];

        $errors = [];
        foreach ($configData as $name => $publisherData) {

            $diff = array_diff($requiredPublisherFields, array_keys($publisherData));
            foreach ($diff as $field) {
                $errors[] = sprintf('Missing %s field for publisher %s.', $field, $name);
            }

            if (!array_key_exists('connections', $publisherData) || !is_array($publisherData['connections'])) {
                $errors[] = sprintf('Invalid connections format for publisher %s.', $name);
                continue;
            }

            foreach ($publisherData['connections'] as $connectionConfig) {
                $diff = array_diff($requiredConnectionFields, array_keys($connectionConfig));
                foreach ($diff as $field) {
                    $errors[] = sprintf('Missing %s field for publisher %s in connection config.', $field, $name);
                }
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(' ', $errors));
        }
    }
}
