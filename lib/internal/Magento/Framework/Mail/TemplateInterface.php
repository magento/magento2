<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

/**
 * Mail Template interface
 *
 * @api
 * @since 2.0.0
 */
interface TemplateInterface extends \Magento\Framework\App\TemplateTypesInterface
{
    /**
     * Get processed template
     *
     * @return string
     * @since 2.0.0
     */
    public function processTemplate();

    /**
     * Get processed subject
     *
     * @return string
     * @since 2.0.0
     */
    public function getSubject();

    /**
     * Set template variables
     *
     * @param array $vars
     * @return $this
     * @since 2.0.0
     */
    public function setVars(array $vars);

    /**
     * Set template options
     *
     * @param array $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions(array $options);
}
