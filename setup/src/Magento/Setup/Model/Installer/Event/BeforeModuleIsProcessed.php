<?php

namespace Magento\Setup\Model\Installer\Event;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SetupInterface;
use Zend\EventManager\Exception\InvalidArgumentException;

/**
 * Event fired during setup before each module is processed
 */
class BeforeModuleIsProcessed extends AbstractInstallerEvent
{
    /** @var string */
    private $installType;

    /** @var ModuleContextInterface */
    private $context;

    /**
     * @param object|string $target
     * @param SetupInterface $setup
     * @param ModuleContextInterface $context
     * @param string $installType
     */
    public function __construct($target, SetupInterface $setup, ModuleContextInterface $context, $installType)
    {
        if (!\is_string($installType)) {
            throw new InvalidArgumentException(sprintf(
                'Expected parameter $installType to be a string.'
            ));
        }

        $this->internalSetTarget($target);
        $this->setSetup($setup);
        $this->context = $context;
        $this->installType = (string) $installType;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return 'setup:before_module_is_processed';
    }

    /**
     * Get parameters passed to the event
     *
     * @return array|\ArrayAccess
     */
    public function getParams()
    {
        return [
            'target' => $this->getTarget(),
            'setup' => $this->getSetup(),
            'context' => $this->getContext(),
            'installType' => $this->getInstallType(),
        ];
    }

    /**
     * @return string
     */
    public function getInstallType()
    {
        return $this->installType;
    }

    /**
     * @return ModuleContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
