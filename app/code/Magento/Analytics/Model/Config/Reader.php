<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * Composite reader for config.
 */
class Reader implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @param Mapper $mapper
     * @param ReaderInterface[] $readers
     */
    public function __construct(
        Mapper $mapper,
        $readers = []
    ) {
        $this->mapper = $mapper;
        $this->readers = $readers;
    }

    /**
     * Read configuration scope.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $data = [];
        foreach ($this->readers as $reader) {
            $data = array_merge_recursive($data, $reader->read($scope));
        }

        return $this->mapper->execute($data);
    }
}
