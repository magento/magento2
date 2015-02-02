<?php
/**
 * Mail Template interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

interface TemplateInterface extends \Magento\Framework\App\TemplateTypesInterface
{
    /**
     * Get processed template
     *
     * @return string
     */
    public function processTemplate();

    /**
     * Get processed subject
     *
     * @return string
     */
    public function getSubject();

    /**
     * Set template variables
     *
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars);

    /**
     * Set template options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options);
}
