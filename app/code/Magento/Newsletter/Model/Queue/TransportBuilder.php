<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Queue;

use Magento\Email\Model\AbstractTemplate;
use Magento\Framework\Exception\LocalizedException;

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

        $body = $template->getProcessedTemplate($this->templateVars);
        switch ($template->getType()) {
            case \Magento\Framework\Mail\MessageInterface::TYPE_TEXT:
                $this->message->setBodyText($body);
                break;

            case \Magento\Framework\Mail\MessageInterface::TYPE_HTML:
                $this->message->setBodyHtml($body);
                break;

            default;
                throw new LocalizedException(
                    __('Unknown template type')
                );
                break;
        }

        $this->message->setSubject($template->getSubject());

        return $this;
    }
}
