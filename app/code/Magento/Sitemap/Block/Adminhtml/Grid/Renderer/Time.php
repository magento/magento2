<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace Magento\Sitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Time extends AbstractRenderer
{
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateTime $date,
        array $data = []
    ) {
        $this->_date = $date;
        parent::__construct($context, $data);
    }

    /**
     * Prepare link to display in grid
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $time = date('Y-m-d H:i:s', strtotime($row->getSitemapTime()) + $this->_date->getGmtOffset());
        return $time;
    }
}
