<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Phrase;

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
        $this->readers = [];
        $readers = $this->sortReaders($readers);
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

    /**
     * Sort readers according to param 'sortOrder'
     *
     * @param array $readers
     * @return array
     * @since 2.1.0
     */
    private function sortReaders(array $readers)
    {
        usort(
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
