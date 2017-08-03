<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Search;

/**
 * @api
 * @since 2.0.0
 */
class Proxy implements
    \Magento\Config\Model\Config\Structure\SearchInterface,
    \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var \Magento\Config\Model\Config\Structure
     * @since 2.0.0
     */
    protected $_subject;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Retrieve subject
     *
     * @return \Magento\Config\Model\Config\Structure\SearchInterface
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getElement($path)
    {
        return $this->_getSubject()->getElement($path);
    }
}
