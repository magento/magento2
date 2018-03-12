<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ReaderInterface;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;

/**
 * Composite reader for consumer config.
 */
class CompositeReader implements ReaderInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * Initialize dependencies.
     *
     * @param ValidatorInterface $validator
     * @param ReaderInterface[] $readers
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
