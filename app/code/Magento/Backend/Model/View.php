<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

class View extends \Magento\Framework\App\View
{
    /**
     * @var Layout\Filter\Acl
     */
    protected $_aclFilter;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param View\Layout\Filter\Acl $aclFilter
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\ActionFlag $actionFlag,
        View\Layout\Filter\Acl $aclFilter
    ) {
        $this->_aclFilter = $aclFilter;
        parent::__construct($layout, $request, $response, $configScope, $eventManager, $pageFactory, $actionFlag);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true)
    {
        parent::loadLayout($handles, false, $generateXml, $addActionHandles);
        $this->_aclFilter->filterAclNodes($this->getLayout()->getNode());
        if ($generateBlocks) {
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
        }
        $this->getLayout()->initMessages();
        return $this;
    }

    /**
     * Returns is layout loaded
     *
     * @return bool
     */
    public function isLayoutLoaded()
    {
        return $this->_isLayoutLoaded;
    }
}
