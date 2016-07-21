<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Validator;

use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;

/**
 * Publisher config data validator. Validates that publisher has only one enabled connection at the same time
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

            foreach ($requiredPublisherFields as $field) {
                if (!array_key_exists($field, $publisherData)) {
                    $errors[] = sprintf('Missing %s field for publisher %s.', $field, $name);
                }
            }

            if (!array_key_exists('connections', $publisherData) || !is_array($publisherData['connections'])) {
                $errors[] = sprintf('Invalid connections format for publisher %s.', $name);
                continue;
            }

            foreach ($publisherData['connections'] as $connectionConfig) {
                foreach ($requiredConnectionFields as $field) {
                    if (!array_key_exists($field, $connectionConfig)) {
                        $errors[] = sprintf('Missing %s field for publisher %s in connection config.', $field, $name);
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(' ', $errors));
        }
    }
}
