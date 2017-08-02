<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Queue;

use Magento\Email\Model\AbstractTemplate;

/**
 * Class \Magento\Newsletter\Model\Queue\TransportBuilder
 *
 * @since 2.0.0
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Template data
     *
     * @var array
     * @since 2.0.0
     */
    protected $templateData = [];

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * @param AbstractTemplate $template
     * @return void
     * @since 2.0.0
     */
    protected function setTemplateFilter(AbstractTemplate $template)
    {
        if (isset($this->templateData['template_filter'])) {
            $template->setTemplateFilter($this->templateData['template_filter']);
        }
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function prepareMessage()
    {
        /** @var AbstractTemplate $template */
        $template = $this->getTemplate()->setData($this->templateData);
        $this->setTemplateFilter($template);

        $this->message->setMessageType(
            \Magento\Framework\Mail\MessageInterface::TYPE_HTML
        )->setBody(
            $template->getProcessedTemplate($this->templateVars)
        )->setSubject(
            $template->getSubject()
        );

        return $this;
    }
}
