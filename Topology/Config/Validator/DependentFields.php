<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Validator;

use Magento\Framework\MessageQueue\Topology\Config\ValidatorInterface;

/**
 * Topology config data validator.
 * @since 2.2.0
 */
class DependentFields implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function validate($configData)
    {
        $errors = [];
        foreach ($configData as $name => $data) {
            foreach ((array)$data['bindings'] as $binding) {
                if (isset($data['type']) && $data['type'] == 'topic' && !isset($binding['topic'])) {
                    $errors[] = 'Topic name is required for topic based exchange: ' . $name;
                }
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(PHP_EOL, $errors));
        }
    }
}
