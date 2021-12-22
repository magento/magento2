<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block;

/**
 * Class used to initialize layout for MBO Dashboard
 * @deprecated 102.0.0 dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard in adminhtml_dashboard_index.xml
 *
 * @api
 * @since 100.0.2
 */
class Dashboard extends Template
{
    /**
     * Location of the "Enable Chart" config param
     */
    const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/index.phtml';

    /**
     * Get url for switch action
     *
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
