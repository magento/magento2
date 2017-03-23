<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

/**
 * High-level interface for mail templates data that hides format from the client code
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Retrieve unique identifiers of all available email templates
     *
     * @return string[]
     */
    public function getAvailableTemplates();

    /**
     * Retrieve translated label of an email template
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateLabel($templateId);

    /**
     * Retrieve type of an email template
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateType($templateId);

    /**
     * Retrieve fully-qualified name of a module an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateModule($templateId);

    /**
     * Retrieve the area an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateArea($templateId);

    /**
     * Retrieve full path to an email template file
     *
     * @param string $templateId
     * @param array|null $designParams
     * @return string
     */
    public function getTemplateFilename($templateId, $designParams = []);
}
