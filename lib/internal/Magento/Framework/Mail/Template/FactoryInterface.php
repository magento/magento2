<?php
/**
 * Mail Template Factory interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
