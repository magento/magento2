<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Email\Container;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class Template implements ResetAfterRequestInterface
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
     * @var int|string
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
     * @param int|string $id
     * @return void
     */
    public function setTemplateId($id)
    {
        $this->id = $id;
    }

    /**
     * Get email template id
     *
     * @return int|string
     */
    public function getTemplateId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->vars = null;
        $this->options = null;
        $this->id = null;
        $this->templateId = null;
    }
}
