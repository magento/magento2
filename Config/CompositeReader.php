<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Phrase;

/**
 * Composite reader for communication config.
 */
class CompositeReader implements ReaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;

    /**
     * Initialize dependencies.
     *
     * @param array $readers
     */
    public function __construct(array $readers)
    {
        usort(
            $readers,
            function ($firstItem, $secondItem) {
                $firstValue = isset($firstItem['sortOrder']) ? intval($firstItem['sortOrder']) : 0;
                $secondValue = isset($secondItem['sortOrder']) ? intval($secondItem['sortOrder']) : 0;
                if ($firstValue == $secondValue) {
                    return 0;
                }
                return $firstValue < $secondValue ? -1 : 1;
            }
        );
        $this->readers = [];
        foreach ($readers as $name => $readerInfo) {
            if (!isset($readerInfo['reader']) || !($readerInfo['reader'] instanceof ReaderInterface)) {
                throw new \InvalidArgumentException(
                    new Phrase(
                        'Reader [%name] must implement Magento\Framework\Config\ReaderInterface',
                        ['name' => $name]
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
        return $result;
    }
}
