<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
declare(strict_types=1);

namespace Magento\Security\Block\Config\Backend\Session;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Backend Model for Max Session Size
 */
class SessionSize extends Field
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->json = $json;
    }

    /**
     * {@inheritdoc}
     * @throws ValidatorException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $originalData = $element->getOriginalData();
        $maxSessionSizeAdminSelector = '#' . $element->getHtmlId();
        $jsString = '<script type="text/x-magento-init"> {"' .
            $maxSessionSizeAdminSelector . '": {
            "Magento_Security/js/system/config/session-size": {"modalTitleText": ' .
            $this->json->serialize(__($originalData['modal_title_text'])) . ', "modalContentBody": ' .
            $this->json->serialize($this->getModalContentBody($originalData['modal_content_body_path']))
            . '}}}</script>';

        $html .= $jsString;
        return $html;
    }

    /**
     * Get HTML for the modal content body when user switches to disable
     *
     * @param string $templatePath
     * @return string
     * @throws ValidatorException
     */
    private function getModalContentBody(string $templatePath)
    {
        $templateFileName = $this->getTemplateFile($templatePath);

        return $this->fetchView($templateFileName);
    }
}
