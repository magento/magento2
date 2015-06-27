<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return \Magento\Framework\Mail\TemplateInterface
     */
    public function get($identifier);
}
