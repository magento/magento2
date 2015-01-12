<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\PageCache\Block\System\Config\Form\Field\Export;

/**
 * Class Export
 */
class Varnish3 extends \Magento\PageCache\Block\System\Config\Form\Field\Export
{
    /*
     * Return Varnish version to this class
     *
     * @return int
     */
    public function getVarnishVersion()
    {
        return 3;
    }
}
