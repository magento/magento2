<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Xsd\Media;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\View\Xsd\Media\TypeDataExtractorPool
 *
 * @since 2.0.0
 */
class TypeDataExtractorPool
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * List of extractors
     *
     * @var array
     * @since 2.0.0
     */
    protected $extractors = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Xsd\Media\TypeDataExtractorInterface[] $extractors
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function nodeProcessor($tagName)
    {
        if (isset($this->extractors[$tagName])) {
            return $this->extractors[$tagName];
        }
    }
}
