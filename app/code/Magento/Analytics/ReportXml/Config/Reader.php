<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * Class Reader
 *
 * * Composite reader for config
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
     * Reader constructor.
     *
     * @param Mapper $mapper
     * @param array $readers
     */
    public function __construct(
        Mapper $mapper,
        $readers = []
    ) {
        $this->readers = $readers;
        $this->mapper = $mapper;
    }

    /**
     * Read configuration scope
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
