<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * A composite reader of reports configuration.
 *
 * Reads configuration data using declared readers.
 */
class Reader implements ReaderInterface
{
    /**
     * A list of declared readers.
     *
     * The list may be configured in each module via '/etc/di.xml'.
     *
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
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
     * Reads configuration according to the given scope.
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
