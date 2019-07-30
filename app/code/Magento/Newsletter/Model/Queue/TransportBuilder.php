<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Queue;

use Magento\Email\Model\AbstractTemplate;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Template data
     *
     * @var array
     */
    protected $templateData = [];

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * @param AbstractTemplate $template
     * @return void
     */
    protected function setTemplateFilter(AbstractTemplate $template)
    {
        if (isset($this->templateData['template_filter'])) {
            $template->setTemplateFilter($this->templateData['template_filter']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareMessage()
    {
        /** @var AbstractTemplate $template */
        $template = $this->getTemplate()->setData($this->templateData);
        $this->setTemplateFilter($template);
        $part['content'] = $template->getProcessedTemplate($this->templateVars);
        $this->messageData['body'][] = $part;
        $this->messageData['subject'] = $template->getSubject();
        $this->message = $this->mailEnvelopeBuilder->buildByArray($this->messageData);

        return $this;
    }
}
