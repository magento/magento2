<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File;

use Magento\Framework\View\File;
use Magento\Framework\View\File\FileList\CollateInterface;

/**
 * Unordered list of view file instances with awareness of view file identity
 */
class FileList
{
    /**
     * Array of files
     *
     * @var File[]
     */
    protected $files = [];

    /**
     * Collator
     *
     * @var \Magento\Framework\View\File\FileList\CollateInterface
     */
    protected $collator;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\File\FileList\CollateInterface $collator
     */
    public function __construct(CollateInterface $collator)
    {
        $this->collator = $collator;
    }

    /**
     * Retrieve all view file instances
     *
     * @return File[]
     */
    public function getAll()
    {
        return array_values($this->files);
    }

    /**
     * Add view file instances to the list, preventing identity coincidence
     *
     * @param \Magento\Framework\View\File[] $files
     * @return void
     * @throws \LogicException
     */
    public function add(array $files)
    {
        foreach ($files as $file) {
            $identifier = $file->getFileIdentifier();
            if (array_key_exists($identifier, $this->files)) {
                $filename = $this->files[$identifier]->getFilename();
                throw new \LogicException(
                    "View file '{$file->getFilename()}' is indistinguishable from the file '{$filename}'."
                );
            }
            $this->files[$identifier] = $file;
        }
    }

    /**
     * Replace already added view files with specified ones, checking for identity match
     *
     * @param File[] $files
     * @return void
     */
    public function replace(array $files)
    {
        $this->files = $this->collator->collate($files, $this->files);
    }
}
