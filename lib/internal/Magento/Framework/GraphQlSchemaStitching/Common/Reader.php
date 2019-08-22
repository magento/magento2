<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\Common;

use Magento\Framework\Config\ReaderInterface;

/**
 * Composite configuration reader for GraphQL schema information.
 */
class Reader implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(
        array $readers = []
    ) {
        $this->readers = $readers;
    }

    /**
     * Read configuration scope for GraphQL.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null) : array
    {
        $output = [];
        foreach ($this->readers as $reader) {
            $output = array_replace_recursive($output, $reader->read($scope));
        }
        return $output;
    }
}
