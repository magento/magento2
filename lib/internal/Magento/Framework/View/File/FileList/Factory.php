<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\FileList;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory that produces view file list instances
 * @since 2.0.0
 */
class Factory
{
    /**
     * Default file list collator
     */
    const FILE_LIST_COLLATOR = \Magento\Framework\View\File\FileList\Collator::class;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($instanceName = self::FILE_LIST_COLLATOR)
    {
        $collator = $this->objectManager->get($instanceName);
        if (!$collator instanceof CollateInterface) {
            throw new \UnexpectedValueException("$instanceName has to implement the collate interface.");
        }
        return $this->objectManager->create(\Magento\Framework\View\File\FileList::class, ['collator' => $collator]);
    }
}
