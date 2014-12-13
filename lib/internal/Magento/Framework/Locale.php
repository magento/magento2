<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
