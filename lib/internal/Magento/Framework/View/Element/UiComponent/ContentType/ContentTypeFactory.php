<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ContentTypeFactory
 */
class ContentTypeFactory
{
    /**
     * Content types
     *
     * @var array
     */
    protected $types;

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
     * @param array $types
     */
    public function __construct(ObjectManagerInterface $objectManager, array $types)
    {
        $this->types = $types;
        $this->objectManager = $objectManager;
    }

    /**
     * Get content type object instance
     *
     * @param string $type
     * @return ContentTypeInterface
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException(sprintf("Wrong content type '%s', renderer not exists.", $type));
        }

        $contentRender = $this->objectManager->get($this->types[$type]);
        if (!$contentRender instanceof ContentTypeInterface) {
            throw new \InvalidArgumentException(
                sprintf('"%s" must implement the interface ContentTypeInterface.', $this->types[$type])
            );
        }

        return $contentRender;
    }
}
