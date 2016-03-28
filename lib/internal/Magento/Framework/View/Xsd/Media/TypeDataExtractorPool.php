<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Xsd\Media;

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
     * @param \Magento\Framework\View\Xsd\Media\TypeDataExtractorInterface[] $extractors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $extractors
    ) {
        foreach ($extractors as $key => $extractorItem) {
            if (!($extractorItem instanceof TypeDataExtractorInterface)) {
                throw new \InvalidArgumentException('Passed wrong parameters type');
            }
            $this->extractors[$key] = $extractorItem;
        }
        $this->objectManager = $objectManager;
    }

    /**
     * Get node processor from corresponding module
     *
     * @param string $tagName
     * @return object
     */
    public function nodeProcessor($tagName)
    {
        if (isset($this->extractors[$tagName])) {
            return $this->extractors[$tagName];
        }
    }
}
