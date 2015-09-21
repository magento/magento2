<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

class UriFactory
{

    /**
     * @return \Zend\Uri\Uri
     */
    public function create()
    {
        return new \Zend\Uri\Uri();
    }
}
