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
 * A generic layout response can be used for rendering any kind of layout
 * So it comprises a response body from the layout elements it has and sets it to the HTTP response
 */
class Layout extends View\Element\Template
    //implements ResultInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @param View\Element\Template\Context $context
     * @param View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param array $data
     */
    public function __construct(
        View\Element\Template\Context $context,
        View\LayoutFactory $layoutFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        array $data = array()
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->translateInline = $translateInline;
        parent::__construct($context, $data);
    }

    /**
     * Get layout instance for current page
     *
     * TODO: This layout model must be isolated, now are used shared instance of layout (MAGETWO-26282)
     *
     * @return \Magento\Framework\View\Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Create new instance of layout for current page
     *
     * @return $this
     */
    public function initLayout()
    {
        $this->layout = $this->layoutFactory->create();
        return $this;
    }

    /**
     * Render current layout
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        $response->appendBody($this->_layout->getOutput());
        return $this;
    }
}
