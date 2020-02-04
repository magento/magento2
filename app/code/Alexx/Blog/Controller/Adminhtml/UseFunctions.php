<?php

namespace Alexx\Blog\Controller\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Trait for frequently called fields
 * */
trait UseFunctions
{
    protected $_objectManager;
    protected $_session;
    protected $_coreRegistry;

    /**
     * Get registry or registry value
     *
     * @param string|null $target
     * */
    public function getCurrentRegistry($target = null)
    {
        if (!$this->_coreRegistry) {
            $this->_coreRegistry = $this->getClassFromObjectManager(Registry::class);
        }
        if ($target) {
            return $this->_coreRegistry->registry($target);
        } else {
            return $this->_coreRegistry;
        }
    }

    /**
     * Get session
     * */
    public function getCurrentSession()
    {
        if (!$this->_session) {
            $this->_session = $this->getClassFromObjectManager(SessionManagerInterface::class);
        }
        return $this->_session;
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $type
     * @return mixed
     */
    public function getClassFromObjectManager($type)
    {
        return $this->getCurrentObjectManager()->get($type);
    }


    /**
     * Get Object Manager
     * */
    public function getCurrentObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = ObjectManager::getInstance();
        }
        return $this->_objectManager;
    }
}
