<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\System\Config\Form\Field\Export;

/**
 * Class Export
 */
class Varnish5 extends \Magento\PageCache\Block\System\Config\Form\Field\Export
{
    /**
     * Return Varnish version to this class
     *
     * @return int
     */
    public function getVarnishVersion()
    {
        return 5;
    }
}
