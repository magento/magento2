<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

class Locale extends \Zend_Locale implements \Magento\Framework\LocaleInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct($locale = null)
    {
        parent::__construct($locale);
    }
}
