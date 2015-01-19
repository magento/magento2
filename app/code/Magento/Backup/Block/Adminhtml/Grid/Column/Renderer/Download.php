<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup grid item renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Block\Adminhtml\Grid\Column\Renderer;

class Download extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\Object $row)
    {
        $url7zip = __(
            'The archive can be uncompressed with <a href="%1">%2</a> on Windows systems.',
            'http://www.7-zip.org/',
            '7-Zip'
        );

        return '<a href="' . $this->getUrl(
            '*/*/download',
            ['time' => $row->getData('time'), 'type' => $row->getData('type')]
        ) . '">' . $row->getData(
            'extension'
        ) . '</a> &nbsp; <small>(' . $url7zip . ')</small>';
    }
}
