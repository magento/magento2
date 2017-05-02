<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Block;

/**
 * @api
 */
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
        $this->addChild('lastOrders', \Magento\Backend\Block\Dashboard\Orders\Grid::class);

        $this->addChild('totals', \Magento\Backend\Block\Dashboard\Totals::class);

        $this->addChild('sales', \Magento\Backend\Block\Dashboard\Sales::class);

        $isChartEnabled = $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLE_CHARTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isChartEnabled) {
            $block = $this->getLayout()->createBlock(\Magento\Backend\Block\Dashboard\Diagrams::class);
        } else {
            $block = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Template::class
            )->setTemplate(
                'dashboard/graph/disabled.phtml'
            )->setConfigUrl(
                $this->getUrl(
                    'adminhtml/system_config/edit',
                    ['section' => 'admin', '_fragment' => 'admin_dashboard-link']
                )
            );
        }
        $this->setChild('diagrams', $block);

        $this->addChild('grids', \Magento\Backend\Block\Dashboard\Grids::class);

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
        return $this->getUrl('adminhtml/*/*', ['_current' => true, 'period' => null]);
    }
}
