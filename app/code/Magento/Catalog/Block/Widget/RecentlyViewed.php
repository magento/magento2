<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Widget;

use Magento\Ui\Block\Wrapper;

/**
 * Dynamically creates recently viewed widget ui component, using information
 * from widget instance and Catalog/widget.xml
 */
class RecentlyViewed extends Wrapper implements \Magento\Widget\Block\BlockInterface
{
    protected const RENDER_TYPE = 'html';
}
