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
class Format implements ValidatorInterface
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

            if (!array_key_exists('topic', $publisherData)) {
                $errors[] = sprintf('Missed topic field for publisher %s.', $name);
            }

            if (!array_key_exists('disabled', $publisherData)) {
                $errors[] = sprintf('Missed disabled field for publisher %s.', $name);
            }

            if (!array_key_exists('connections', $publisherData)) {
                $errors[] = sprintf('Missed connections field for publisher %s.', $name);
            } else {

                if (!is_array($publisherData['connections'])) {
                    $errors[] = sprintf('Invalid connections format for publisher %s.', $name);
                    continue;
                }

                foreach ($publisherData['connections'] as $connectionConfig) {
                    if (!array_key_exists('disabled', $connectionConfig)) {
                        $errors[] = sprintf('Missed disabled field for publisher %s in connection config.', $name);
                    }

                    if (!array_key_exists('exchange', $connectionConfig)) {
                        $errors[] = sprintf('Missed exchange field for publisher %s in connection config.', $name);
                    }

                    if (!array_key_exists('name', $connectionConfig)) {
                        $errors[] = sprintf('Missed name field for publisher %s in connection config.', $name);
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(' ', $errors));
        }
    }
}
