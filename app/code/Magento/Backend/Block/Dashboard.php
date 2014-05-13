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
namespace Magento\Backend\Block;

class Dashboard extends \Magento\Backend\Block\Template
{
    /**
     * Location of the "Enable Chart" config param
     */
    const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';

    /**
     * @var string
     */
    protected $_template = 'dashboard/index.phtml';

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addChild('lastOrders', 'Magento\Backend\Block\Dashboard\Orders\Grid');

        $this->addChild('totals', 'Magento\Backend\Block\Dashboard\Totals');

        $this->addChild('sales', 'Magento\Backend\Block\Dashboard\Sales');

        $this->addChild('lastSearches', 'Magento\Backend\Block\Dashboard\Searches\Last');

        $this->addChild('topSearches', 'Magento\Backend\Block\Dashboard\Searches\Top');

        if ($this->_scopeConfig->getValue(self::XML_PATH_ENABLE_CHARTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $block = $this->getLayout()->createBlock('Magento\Backend\Block\Dashboard\Diagrams');
        } else {
            $block = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Template'
            )->setTemplate(
                'dashboard/graph/disabled.phtml'
            )->setConfigUrl(
                $this->getUrl('adminhtml/system_config/edit', array('section' => 'admin'))
            );
        }
        $this->setChild('diagrams', $block);

        $this->addChild('grids', 'Magento\Backend\Block\Dashboard\Grids');

        parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('adminhtml/*/*', array('_current' => true, 'period' => null));
    }
}
