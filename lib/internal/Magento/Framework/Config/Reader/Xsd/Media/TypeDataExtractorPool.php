<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Reader\Xsd\Media;

use Magento\Framework\ObjectManagerInterface;


class TypeDataExtractorPool
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
     * @param ObjectManagerInterface $objectManager
     * @param @param \Magento\Framework\Config\Reader\Xsd\MediaTypeDataExtractorInterface[] $extractors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $extractors
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
