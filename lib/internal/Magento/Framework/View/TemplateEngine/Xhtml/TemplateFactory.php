<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml;

use Magento\Framework\Phrase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TemplateFactory
 * @since 2.0.0
 */
class TemplateFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Instance name
     *
     * @var string
     * @since 2.0.0
     */
    protected $instanceName;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\View\TemplateEngine\Xhtml\Template::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create result
     *
     * @param array $arguments
     * @return Template
     * @throws LocalizedException
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        $object = $this->objectManager->create($this->instanceName, $arguments);

        if (!($object instanceof Template)) {
            throw new LocalizedException(new Phrase('This class must inherit from a class "Template"'));
        }

        return $object;
    }
}
