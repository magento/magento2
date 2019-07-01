<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class WebsiteName
 */
class WebsiteName extends AbstractNameColumn
{
    /**
     * @inheritdoc
     */
    public function prepareTitle(array $item)
    {
        $fieldName = $this->getData('name');
        $url = $this->context->getUrl(
            'adminhtml/system_store/editWebsite',
            ['website_id' => $item['website_id']]
        );

        $html =  '<a title="' . __('Edit Store') . '" href="' . $url . '">' .
            $item[$fieldName]. '</a><br />' . '(' . __('Code') . ': ' . $item['code'] . ')';

        return $html;
    }
}
