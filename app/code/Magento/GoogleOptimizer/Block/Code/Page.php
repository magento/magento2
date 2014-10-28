<?php
/**
 * Google Optimizer Page Block
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Block\Code;

class Page extends \Magento\GoogleOptimizer\Block\AbstractCode
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GoogleOptimizer\Helper\Code $codeHelper
     * @param \Magento\Cms\Model\Page $page
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GoogleOptimizer\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\GoogleOptimizer\Helper\Code $codeHelper,
        \Magento\Cms\Model\Page $page,
        array $data = array()
    ) {
        // \Magento\Cms\Model\Page is singleton
        $this->_page = $page;
        parent::__construct($context, $helper, $registry, $codeHelper, $data);
    }

    /**
     * Get cms page entity
     *
     * @return \Magento\Cms\Model\Page
     */
    protected function _getEntity()
    {
        return $this->_page;
    }
}
