<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class View implements ViewInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var bool
     */
    protected $_isLayoutLoaded = false;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        RequestInterface $request,
        ResponseInterface $response,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ActionFlag $actionFlag
    ) {
        $this->_layout = $layout;
        $this->_request = $request;
        $this->_response = $response;
        $this->_configScope = $configScope;
        $this->_eventManager = $eventManager;
        $this->_actionFlag = $actionFlag;
        $this->page = $pageFactory->create(true);
    }

    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->page->getLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true)
    {
        if ($this->_isLayoutLoaded) {
            throw new \RuntimeException('Layout must be loaded only once.');
        }
        // if handles were specified in arguments load them first
        if (!empty($handles)) {
            $this->getLayout()->getUpdate()->addHandle($handles);
        }

        if ($addActionHandles) {
            // add default layout handles for this action
            $this->page->initLayout();
        }
        $this->loadLayoutUpdates();

        if (!$generateXml) {
            return $this;
        }
        $this->generateLayoutXml();

        if (!$generateBlocks) {
            return $this;
        }
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        return $this;
    }

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        return $this->page->getDefaultLayoutHandle();
    }

    /**
     * Add layout handle by full controller action name
     *
     * @return $this
     */
    public function addActionLayoutHandles()
    {
        $this->getLayout()->getUpdate()->addHandle($this->getDefaultLayoutHandle());
        return $this;
    }

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array|null $parameters page parameters
     * @param string|null $defaultHandle
     * @return bool
     */
    public function addPageLayoutHandles(array $parameters = [], $defaultHandle = null)
    {
        return $this->page->addPageLayoutHandles($parameters, $defaultHandle);
    }

    /**
     * Load layout updates
     *
     * @return $this
     */
    public function loadLayoutUpdates()
    {
        $this->page->getConfig()->publicBuild();
        return $this;
    }

    /**
     * Generate layout xml
     *
     * @return $this
     */
    public function generateLayoutXml()
    {
        $this->page->getConfig()->publicBuild();
        return $this;
    }

    /**
     * Generate layout blocks
     *
     * @return $this
     */
    public function generateLayoutBlocks()
    {
        $this->page->getConfig()->publicBuild();
        return $this;
    }

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  $this
     */
    public function renderLayout($output = '')
    {
        if ($this->_actionFlag->get('', 'no-renderLayout')) {
            return $this;
        }

        \Magento\Framework\Profiler::start('LAYOUT');

        \Magento\Framework\Profiler::start('layout_render');

        if ('' !== $output) {
            $this->getLayout()->addOutputElement($output);
        }

        $this->_eventManager->dispatch('controller_action_layout_render_before');
        $this->_eventManager->dispatch(
            'controller_action_layout_render_before_' . $this->_request->getFullActionName()
        );

        $this->page->renderResult($this->_response);
        \Magento\Framework\Profiler::stop('layout_render');

        \Magento\Framework\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     */
    public function setIsLayoutLoaded($value)
    {
        $this->_isLayoutLoaded = $value;
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
