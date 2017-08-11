<?php

namespace Magento\Setup\Model\Installer\Event;

use ArrayAccess;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Setup\SetupInterface;

/**
 * Event fired after data was installed during setup
 */
class AfterDataWasInstalled extends AbstractInstallerEvent
{
    /** @var Context */
    private $context;

    /**
     * @param string|object $target
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
        return 'setup:after_data_was_installed';
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
