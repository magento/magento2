<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * Composite reader for communication config.
 * @since 2.1.0
 */
class CompositeReader implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     * @since 2.1.0
     */
    private $readers;

    /**
     * Initialize dependencies.
     *
     * @param array $readers
     * @since 2.1.0
     */
    public function __construct(array $readers)
    {
        usort(
            $readers,
            function ($firstItem, $secondItem) {
                if (!isset($firstItem['sortOrder']) || !isset($secondItem['sortOrder'])
                    || $firstItem['sortOrder'] == $secondItem['sortOrder']
                ) {
                    return 0;
                }
                return $firstItem['sortOrder'] < $secondItem['sortOrder'] ? -1 : 1;
            }
        );
        $this->readers = [];
        foreach ($readers as $readerInfo) {
            if (!isset($readerInfo['reader'])) {
                continue;
            }
            $this->readers[] = $readerInfo['reader'];
        }
    }

    /**
     * Read config.
     *
     * @param string|null $scope
     * @return array
     * @since 2.1.0
     */
    public function read($scope = null)
    {
        $result = [];
        foreach ($this->readers as $reader) {
            $result = array_replace_recursive($result, $reader->read($scope));
        }
        return $result;
    }
}
