<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Validator;

use Magento\Framework\MessageQueue\Topology\Config\ValidatorInterface;

/**
 * Topology config data validator.
 */
class DependentFields implements ValidatorInterface
{
    /**
     * {@inheritdoc}
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
