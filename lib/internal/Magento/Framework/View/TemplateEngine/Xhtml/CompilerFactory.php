<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml;

use Magento\Framework\Phrase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CompilerFactory
 */
class CompilerFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create result
     *
     * @param array $arguments
     * @return CompilerInterface
     * @throws LocalizedException
     */
    public function create(array $arguments = [])
    {
        $object = $this->objectManager->create($this->instanceName, $arguments);

        if (!($object instanceof CompilerInterface)) {
            throw new LocalizedException(new Phrase('This class must implement the "CompilerInterface"'));
        }

        return $object;
    }
}
