<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ContentTypeFactory
 */
class ContentTypeFactory
{
    /**
     * Default content type
     */
    const DEFAULT_TYPE = 'html';

    /**
     * Content types
     *
     * @var array
     */
    protected $types = [
        'html' => 'Magento\Ui\ContentType\Html',
        'json' => 'Magento\Ui\ContentType\Json',
        'xml' => 'Magento\Ui\ContentType\Xml',
    ];

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(ObjectManagerInterface $objectManager, array $types = [])
    {
        $this->types = array_merge($this->types, $types);
        $this->objectManager = $objectManager;
    }

    /**
     * Get content type object instance
     *
     * @param string $type
     * @return ContentTypeInterface
     * @throws \InvalidArgumentException
     */
    public function get($type = ContentTypeFactory::DEFAULT_TYPE)
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
