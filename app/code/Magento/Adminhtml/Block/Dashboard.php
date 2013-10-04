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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Adminhtml\Block;

class Dashboard extends \Magento\Adminhtml\Block\Template
{
    protected $_locale;

    /**
     * Location of the "Enable Chart" config param
     */
    const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';

    protected $_template = 'dashboard/index.phtml';

    protected function _prepareLayout()
    {
        $this->addChild('lastOrders', 'Magento\Adminhtml\Block\Dashboard\Orders\Grid');

        $this->addChild('totals', 'Magento\Adminhtml\Block\Dashboard\Totals');

        $this->addChild('sales', 'Magento\Adminhtml\Block\Dashboard\Sales');

        $this->addChild('lastSearches', 'Magento\Adminhtml\Block\Dashboard\Searches\Last');

        $this->addChild('topSearches', 'Magento\Adminhtml\Block\Dashboard\Searches\Top');

        if ($this->_storeConfig->getConfig(self::XML_PATH_ENABLE_CHARTS)) {
            $block = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Dashboard\Diagrams');
        } else {
            $block = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Template')
                ->setTemplate('dashboard/graph/disabled.phtml')
                ->setConfigUrl($this->getUrl('adminhtml/system_config/edit', array('section'=>'admin')));
        }
        $this->setChild('diagrams', $block);

        $this->addChild('grids', 'Magento\Adminhtml\Block\Dashboard\Grids');

        parent::_prepareLayout();
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current'=>true, 'period'=>null));
    }
}
