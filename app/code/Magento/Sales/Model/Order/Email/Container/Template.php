<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Container;

/**
 * Class \Magento\Sales\Model\Order\Email\Container\Template
 *
 * @since 2.0.0
 */
class Template
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $vars;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $templateId;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $id;

    /**
     * Set email template variables
     *
     * @param array $vars
     * @return void
     * @since 2.0.0
     */
    public function setTemplateVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * Set email template options
     *
     * @param array $options
     * @return void
     * @since 2.0.0
     */
    public function setTemplateOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get email template variables
     *
     * @return array
     * @since 2.0.0
     */
    public function getTemplateVars()
    {
        return $this->vars;
    }

    /**
     * Get email template options
     *
     * @return array
     * @since 2.0.0
     */
    public function getTemplateOptions()
    {
        return $this->options;
    }

    /**
     * Set email template id
     *
     * @param int $id
     * @return void
     * @since 2.0.0
     */
    public function setTemplateId($id)
    {
        $this->id = $id;
    }

    /**
     * Get email template id
     *
     * @return int
     * @since 2.0.0
     */
    public function getTemplateId()
    {
        return $this->id;
    }
}
