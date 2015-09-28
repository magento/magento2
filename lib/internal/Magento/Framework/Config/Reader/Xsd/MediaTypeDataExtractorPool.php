<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Reader\Xsd;

use Magento\Framework\ObjectManagerInterface;


class MediaTypeDataExtractorPool
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * List of extractors
     *
     * @var array
     */
    protected $extractors = [];

    /**
     * @param @param \Magento\Framework\Config\Reader\Xsd\MediaTypeDataExtractorInterface[] $extractors
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $extractors,
        ObjectManagerInterface $objectManager
    ) {
        $this->extractors = $extractors;
        $this->objectManager = $objectManager;
    }

    /**
     * Get node processor from corresponding module
     *
     * @param $tagName
     * @return mixed
     */
    public function nodeProcessor($tagName)
    {
        return $this->objectManager->create($this->extractors[$tagName]);
    }

}