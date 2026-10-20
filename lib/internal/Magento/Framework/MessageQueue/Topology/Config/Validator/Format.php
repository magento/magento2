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
class Format implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($configData)
    {
        $requiredFields = ['name', 'type', 'connection', 'durable', 'autoDelete', 'internal', 'bindings', 'arguments'];
        $requiredBindingFields = ['id', 'destinationType', 'destination', 'disabled', 'topic', 'arguments'];
        $errors = [];
        foreach ($configData as $name => $data) {
            if (($data['type'] ?? null) === 'queue') {
                array_push($errors, ...$this->getQueueErrors((string)$name, $data));
                continue;
            }

            $diff = array_diff($requiredFields, array_keys($data));
            foreach ($diff as $field) {
                $errors[] = sprintf('Missing [%s] field for exchange %s.', $field, $name);
            }

            if (!array_key_exists('bindings', $data) || !is_array($data['bindings'])) {
                $errors[] = sprintf('Invalid bindings format for exchange %s.', $name);
                continue;
            }

            foreach ($data['bindings'] as $bindingConfig) {
                $diff = array_diff($requiredBindingFields, array_keys($bindingConfig));
                foreach ($diff as $field) {
                    $errors[] = sprintf('Missing [%s] field for binding %s in exchange config.', $field, $name);
                }
            }
        }

        if (!empty($errors)) {
            throw new \LogicException(implode(PHP_EOL, $errors));
        }
    }

    /**
     * Collect format errors of a queue declaration.
     *
     * @param string $name
     * @param array $data
     * @return string[]
     */
    private function getQueueErrors(string $name, array $data): array
    {
        $requiredFields = ['name', 'type', 'connection', 'durable', 'autoDelete', 'arguments'];
        $errors = [];
        foreach (array_diff($requiredFields, array_keys($data)) as $field) {
            $errors[] = sprintf('Missing [%s] field for queue %s.', $field, $name);
        }

        return $errors;
    }
}
