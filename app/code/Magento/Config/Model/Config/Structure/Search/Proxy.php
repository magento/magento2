<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Search;

/**
 * @api
 * @since 100.0.2
 */
class Proxy implements
    \Magento\Config\Model\Config\Structure\SearchInterface,
    \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_subject;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Retrieve subject
     *
     * @return \Magento\Config\Model\Config\Structure\SearchInterface
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = $this->_objectManager->get(\Magento\Config\Model\Config\Structure::class);
        }
        return $this->_subject;
    }

    /**
     * Find element by path
     *
     * @param string $path
     * @return \Magento\Config\Model\Config\Structure\ElementInterface|null
     */
    public function getElement($path)
    {
        return $this->_getSubject()->getElement($path);
    }
}
