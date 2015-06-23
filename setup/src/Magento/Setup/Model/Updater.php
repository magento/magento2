<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Updater passes information to the updater application
 */
class Updater
{
    /**
     * Path to Magento var directory
     *
     * @var string
     */
    private $queueFilePath;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $write;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList, Filesystem $filesystem){
        $this->queueFilePath = '.update_queue.json';
        $this->write = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->write->touch($this->queueFilePath);
    }

    /**
     * @param array $packages
     * @return string
     */
    public function createUpdaterTask($packages){
        try {
            // write to .update_queue.json file
            $existingQueue = $this->readQueue();
            $existingQueue['jobs'][] = ['name' => 'update', 'params' => ['require' => $packages]];
            $this->write->writeFile($this->queueFilePath, json_encode($existingQueue, JSON_PRETTY_PRINT), 'w');
            return '';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function readQueue(){
        try {
            $queueFileContent = $this->write->readFile($this->queueFilePath);
            return json_decode($queueFileContent, true);
        } catch (FileSystemException $e){
            return [];
        }
    }
}
