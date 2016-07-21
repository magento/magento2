<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

use Magento\Framework\Phrase;

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
     * Initialize dependencies.
     *
     * @param ValidatorInterface $validator
     * @param array $readers
     */
    public function __construct(ValidatorInterface $validator, array $readers)
    {
        $this->validator = $validator;
        $this->readers = [];
        $readers = $this->sortReaders($readers);
        foreach ($readers as $name => $readerInfo) {
            if (!isset($readerInfo['reader']) || !($readerInfo['reader'] instanceof ReaderInterface)) {
                throw new \InvalidArgumentException(
                    new Phrase(
                        'Reader [%name] must implement %class',
                        ['name' => $name, 'class' => ReaderInterface::class]
                    )
                );
            }
            $this->readers[] = $readerInfo['reader'];
        }
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

        foreach ($result as $key => &$value) {
            //Find enabled connection
            $connection = null;
            foreach ($value['connections'] as $connectionConfig) {
                if (!$connectionConfig['disabled']) {
                    $connection = $connectionConfig;
                    break;
                }
            }
            $value['connection'] = $connection;
            unset($value['connections']);
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Add default connection.
     *
     * @param array $config
     * @return array
     */
    private function addDefaultConnection(array $config)
    {
        $default = [
            'name' => 'amqp',
            'exchange' => 'magento',
            'disabled' => false,
        ];

        foreach ($config as &$value) {
            if (!isset($value['connections']) || empty($value['connections'])) {
                $value['connections']['amqp'] = $default;
                continue;
            }

            $hasActiveConnection = false;
            /** Find enabled connection */
            foreach ($value['connections'] as $connectionConfig) {
                if (!$connectionConfig['disabled']) {
                    $hasActiveConnection = true;
                    break;
                }
            }
            if (!$hasActiveConnection) {
                $value['connections']['amqp'] = $default;
            }
        }
        return $config;
    }

    /**
     * Sort readers according to param 'sortOrder'
     *
     * @param array $readers
     * @return array
     */
    private function sortReaders(array $readers)
    {
        uasort(
            $readers,
            function ($firstItem, $secondItem) {
                $firstValue = 0;
                $secondValue = 0;
                if (isset($firstItem['sortOrder'])) {
                    $firstValue = intval($firstItem['sortOrder']);
                }

                if (isset($secondItem['sortOrder'])) {
                    $secondValue = intval($secondItem['sortOrder']);
                }

                if ($firstValue == $secondValue) {
                    return 0;
                }
                return $firstValue < $secondValue ? -1 : 1;
            }
        );
        return $readers;
    }
}
