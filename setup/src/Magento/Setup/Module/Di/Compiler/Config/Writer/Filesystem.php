<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Writer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Module\Di\Compiler\Config\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

class Filesystem implements WriterInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write($key, array $config)
    {
        $this->initialize();

        file_put_contents(
            $this->directoryList->getPath(DirectoryList::GENERATED_METADATA) . '/' . $key  . '.ser',
            $this->getSerializer()->serialize($config)
        );
    }

    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::GENERATED_METADATA))) {
            mkdir($this->directoryList->getPath(DirectoryList::GENERATED_METADATA));
        }
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(Serialize::class);
        }
        return $this->serializer;
    }
}
