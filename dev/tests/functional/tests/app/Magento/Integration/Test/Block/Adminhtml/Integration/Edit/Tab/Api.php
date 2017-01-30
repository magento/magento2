<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Integration Api tab.
 */
class Api extends \Magento\Backend\Test\Block\Widget\Tab
{
    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        $resourceAccessValue = $this->_getData([$data['resource_access']])[0];
        if ($resourceAccessValue == 'All') {
            return ['resource_access' => $resourceAccessValue];
        } else {
            return $this->_getData($data, $element);
        }
    }
}
