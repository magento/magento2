<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\FileList;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory that produces view file list instances
 */
class Factory
{
    /**
     * Default file list collator
     */
    const FILE_LIST_COLLATOR = 'Magento\Framework\View\File\FileList\Collator';

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Return newly created instance of a view file list
     *
     * @param string $instanceName
     * @return \Magento\Framework\View\File\FileList
     * @throws \UnexpectedValueException
     */
    public function create($instanceName = self::FILE_LIST_COLLATOR)
    {
        $collator = $this->objectManager->get($instanceName);
        if (!$collator instanceof CollateInterface) {
            throw new \UnexpectedValueException("$instanceName has to implement the collate interface.");
        }
        return $this->objectManager->create('Magento\Framework\View\File\FileList', ['collator' => $collator]);
    }
}
