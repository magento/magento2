<?php
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\MediaStorage\Model\File\Storage\Synchronization
 */
class SynchronizationFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, string $instanceName = '\\Magento\\MediaStorage\\Model\\File\\Storage\\Synchronization')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Synchronization
     */
    public function create(array $data = []): Synchronization
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
