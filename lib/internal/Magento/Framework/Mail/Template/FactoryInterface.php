<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Mail\Template;

/**
 * Mail Template Factory interface
 *
 * @api
 * @since 100.0.2
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
