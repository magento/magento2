<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use Magento\Framework\Phrase;

/**
 * Composite reader for topology config.
 * @since 2.2.0
 */
class CompositeReader implements ReaderInterface
{
    /**
     * Config validator.
     *
     * @var ValidatorInterface
     * @since 2.2.0
     */
    private $validator;

    /**
     * Config reade list.
     *
     * @var ReaderInterface[]
     * @since 2.2.0
     */
    private $readers;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface $validator
     * @param ReaderInterface[] $readers
     * @since 2.2.0
     */
    public function __construct(ValidatorInterface $validator, array $readers)
    {
        $this->validator = $validator;
        $this->readers = $readers;
    }

    /**
     * Read config.
     *
     * @param string|null $scope
     * @return array
     * @since 2.2.0
     */
    public function read($scope = null)
    {
        $result = [];
        foreach ($this->readers as $reader) {
            $result = array_replace_recursive($result, $reader->read($scope));
        }
        $this->validator->validate($result);
        return $result;
    }
}
