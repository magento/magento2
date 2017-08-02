<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\System\Config\Form\Field\Export;

/**
 * Class Export
 * @since 2.0.0
 */
class Varnish4 extends \Magento\PageCache\Block\System\Config\Form\Field\Export
{
    /**
     * Return Varnish version to this class
     *
     * @return int
     * @since 2.0.0
     */
    public function getVarnishVersion()
    {
        return 4;
    }
}
