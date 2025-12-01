<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
