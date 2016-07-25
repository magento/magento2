<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use Magento\Framework\Phrase;

/**
 * Composite reader for topology config.
 */
class CompositeReader implements ReaderInterface
{
    use \Magento\Framework\MessageQueue\Config\SortedList;

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
        $this->readers = $this->sort($readers, ReaderInterface::class, 'reader');
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
