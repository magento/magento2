<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

/**
 * Mail Template Factory interface
 *
 * @api
 */
interface FactoryInterface
{
    /**
     * Returns the mail template associated with the identifier.
     *
     * @param string $identifier
     * @param null|string $namespace
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    public function get($identifier, $namespace = null);
}
