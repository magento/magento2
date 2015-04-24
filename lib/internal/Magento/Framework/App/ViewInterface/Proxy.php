<?php
namespace Magento\Framework\App\ViewInterface;

/**
 * Proxy class for @see \Magento\Framework\App\ViewInterface
 */
class Proxy implements \Magento\Framework\App\ViewInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\Framework\\App\\ViewInterface', $shared = true)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('_subject', '_isShared');
    }

    /**
     * Retrieve ObjectManager from global scope
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\App\ViewInterface
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayoutUpdates()
    {
        return $this->_getSubject()->loadLayoutUpdates();
    }

    /**
     * {@inheritdoc}
     */
    public function renderLayout($output = '')
    {
        return $this->_getSubject()->renderLayout($output);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLayoutHandle()
    {
        return $this->_getSubject()->getDefaultLayoutHandle();
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true)
    {
        return $this->_getSubject()->loadLayout($handles, $generateBlocks, $generateXml, $addActionHandles);
    }

    /**
     * {@inheritdoc}
     */
    public function generateLayoutXml()
    {
        return $this->_getSubject()->generateLayoutXml();
    }

    /**
     * {@inheritdoc}
     */
    public function addPageLayoutHandles(array $parameters = array(), $defaultHandle = null)
    {
        return $this->_getSubject()->addPageLayoutHandles($parameters, $defaultHandle);
    }

    /**
     * {@inheritdoc}
     */
    public function generateLayoutBlocks()
    {
        return $this->_getSubject()->generateLayoutBlocks();
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        return $this->_getSubject()->getPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout()
    {
        return $this->_getSubject()->getLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function addActionLayoutHandles()
    {
        return $this->_getSubject()->addActionLayoutHandles();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsLayoutLoaded($value)
    {
        return $this->_getSubject()->setIsLayoutLoaded($value);
    }

    /**
     * {@inheritdoc}
     */
    public function isLayoutLoaded()
    {
        return $this->_getSubject()->isLayoutLoaded();
    }
}
