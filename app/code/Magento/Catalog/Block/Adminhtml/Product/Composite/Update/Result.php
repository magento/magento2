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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml block for result of catalog product composite update
 * Forms response for a popup window for a case when form is directly submitted
 * for single item
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Composite\Update;

class Result extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    protected $_jsHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsHelper = $jsHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Forms script response
     *
     * @return string
     */
    public function _toHtml()
    {
        $updateResult = $this->_coreRegistry->registry('composite_update_result');
        $resultJson = $this->_jsonEncoder->encode($updateResult);
        $jsVarname = $updateResult->getJsVarName();
        return $this->_jsHelper->getScript(sprintf('var %s = %s', $jsVarname, $resultJson));
    }
}
