<?php
namespace Magento\RemoteStorage\Model\File\Storage;

use Magento\RemoteStorage\Model\Config;
/**
 * Factory class for @see \Magento\RemoteStorage\Model\File\Storage\Synchronization
 */
class SynchronizationFactory extends \Magento\MediaStorage\Model\File\Storage\SynchronizationFactory
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * Factory constructor
     *
     * @param Config $config
     */
    public function __construct( Config $config)
    {
        $this->isEnabled = $config->isEnabled();
    }

    public function create(array $data = [])
    {
        if ($this->isEnabled) {
            return $this->_objectManager->create(Synchronization::class, $data);
        }
        return parent::create($data);
    }
}
