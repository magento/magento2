<?php
/**
 * Mail Template Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

/**
 * Class \Magento\Framework\Mail\Template\Factory
 *
 * @since 2.0.0
 */
class Factory implements \Magento\Framework\Mail\Template\FactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $instanceName = null;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Mail\TemplateInterface::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($identifier, $namespace = null)
    {
        return $this->objectManager->create(
            $namespace ? $namespace : $this->instanceName,
            ['data' => ['template_id' => $identifier]]
        );
    }
}
