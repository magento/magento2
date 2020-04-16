<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Composite reader for publisher config.
 */
class CompositeReader implements ReaderInterface
{
    /**
     * Config validator.
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Config reade list.
     *
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface $validator
     * @param DefaultValueProvider $defaultValueProvider
     * @param ReaderInterface[] $readers
     */
    public function __construct(
        ValidatorInterface $validator,
        DefaultValueProvider $defaultValueProvider,
        array $readers
    ) {
        $this->validator = $validator;
        $this->readers = $readers;
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * Read config.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $result = [];
        foreach ($this->readers as $reader) {
            $result = array_replace_recursive($result, $reader->read($scope));
        }

        $result = $this->addDefaultConnection($result);

        $this->validator->validate($result);

        return $result;
    }

    /**
     * Add default connection.
     *
     * @param array $config
     * @return array
     */
    private function addDefaultConnection(array $config): array
    {
        $defaultConnectionName = $this->defaultValueProvider->getConnection();
        $default = [
            'name' => $defaultConnectionName,
            'exchange' => $this->defaultValueProvider->getExchange(),
            'disabled' => false,
        ];

        foreach ($config as &$value) {
            if (!isset($value['connection']) || empty($value['connection']) || $value['connection']['disabled']) {
                $value['connection'] = $default;
            }
        }

        return $config;
    }
}
