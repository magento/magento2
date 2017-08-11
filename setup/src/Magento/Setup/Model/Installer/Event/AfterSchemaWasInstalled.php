<?php

namespace Magento\Setup\Model\Installer\Event;

use ArrayAccess;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Setup\SetupInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\Exception\InvalidArgumentException;

/**
 * Event fired during setup after schema was installed
 */
class AfterSchemaWasInstalled extends AbstractInstallerEvent
{
    /** @var Context */
    private $context;

    /**
     * BeforeModuleIsProcessed constructor.
     * @param object|string $target
     * @param SetupInterface $setup
     * @param Context $context
     */
    public function __construct($target, SetupInterface $setup, Context $context)
    {
        $this->internalSetTarget($target);
        $this->setSetup($setup);
        $this->context = $context;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return 'setup:after_schema_was_installed';
    }

    /**
     * Get parameters passed to the event
     *
     * @return array|ArrayAccess
     */
    public function getParams()
    {
        return [
            'target' => $this->getTarget(),
            'setup' => $this->getSetup(),
            'context' => $this->getContext(),
        ];
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
