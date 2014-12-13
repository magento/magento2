<?php
/**
 * Mail Template Factory interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Mail\Template;

interface FactoryInterface
{
    /**
     * @param string $identifier
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    public function get($identifier);
}
