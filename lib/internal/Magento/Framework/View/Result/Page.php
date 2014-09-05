<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Result;

use Magento\Framework\View;
use Magento\Framework\App\ResponseInterface;

/**
 * A "page" result that encapsulates page type, page configuration
 * and imposes certain layout handles.
 *
 * The framework convention is that there will be loaded a guaranteed handle for "all pages",
 * then guaranteed handle that corresponds to page type
 * and a guaranteed handle that stands for page layout (a wireframe of a page)
 *
 * Page result is a more specific implementation of a generic layout response
 */
class Page extends Layout
{
    /**
     * Default template
     */
    const DEFAULT_ROOT_TEMPLATE = 'Magento_Theme::root.phtml';

    /**
     * @var string
     */
    protected $pageType;

    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * Constructor
     *
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param View\Page\Config $pageConfig
     * @param string $pageType
     * @param array $data
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        View\Page\Config $pageConfig,
        $pageType,
        array $data = array()
    ) {
        $this->pageConfig = $pageConfig;
        $this->pageType = $pageType;
        parent::__construct($context, $layoutFactory, $translateInline, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function initLayout()
    {
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $update->addHandle($this->getDefaultLayoutHandle());
        if ($update->isLayoutDefined()) {
            $update->removeHandle('default');
        }
        $this->setTemplate(self::DEFAULT_ROOT_TEMPLATE);
        return $this;
    }

    /**
     * @return \Magento\Framework\View\Page\Config
     */
    public function getConfig()
    {
        return $this->pageConfig;
    }

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array|null $parameters page parameters
     * @param string|null $defaultHandle
     * @return bool
     */
    public function addPageLayoutHandles(array $parameters = array(), $defaultHandle = null)
    {
        $handle = $defaultHandle ? $defaultHandle : $this->getDefaultLayoutHandle();
        $pageHandles = array($handle);
        foreach ($parameters as $key => $value) {
            $pageHandles[] = $handle . '_' . $key . '_' . $value;
        }
        // Do not sort array going into add page handles. Ensure default layout handle is added first.
        return $this->getLayout()->getUpdate()->addPageHandles($pageHandles);
    }

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        return strtolower($this->_request->getFullActionName());
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        if ($this->getConfig()->getPageLayout()) {
            $layout = $this->getLayout();
            $config = $this->getConfig();

            $this->assign('headContent', $layout->getBlock('head')->toHtml());
            $this->addDefaultBodyClasses();
            $this->assign('bodyClasses', $config->getElementAttribute($config::ELEMENT_TYPE_BODY, 'classes'));
            $this->assign('bodyAttributes', $config->getElementAttribute($config::ELEMENT_TYPE_BODY, 'attributes'));
            $this->assign('htmlAttributes', $config->getElementAttribute($config::ELEMENT_TYPE_HTML, 'attributes'));

            $output = $layout->getOutput();
            $this->translateInline->processResponseBody($output);
            $this->assign('layoutContent', $output);
            $response->appendBody($this->toHtml());
        } else {
            parent::renderResult($response);
        }
        return $this;
    }

    /**
     * Add default body classes for current page layout
     *
     * @return $this
     */
    protected function addDefaultBodyClasses()
    {
        $config = $this->getConfig();
        $config->addBodyClass($this->_request->getFullActionName('-'));
        $pageLayout = $this->pageConfig->getPageLayout();
        if ($pageLayout) {
            $config->addBodyClass('page-layout-' . $pageLayout);
        }
        return $this;
    }
}
