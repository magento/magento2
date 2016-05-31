<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Container;

class Template
{
    /**
     * @var array
     */
    protected $vars;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var int
     */
    protected $id;

    /**
     * Set email template variables
     *
     * @param array $vars
     * @return void
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
     */
    public function setTemplateOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get email template variables
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->vars;
    }

    /**
     * Get email template options
     *
     * @return array
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
     */
    public function setTemplateId($id)
    {
        $this->id = $id;
    }

    /**
     * Get email template id
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->id;
    }
}
