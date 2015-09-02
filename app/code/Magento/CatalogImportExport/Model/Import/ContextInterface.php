<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Model\Import;

/**
 * Interface ContextInterface
 * @package Magento\CatalogImportExport\Model\Import
 */
interface ContextInterface
{
    /**
     * Get context params
     *
     * @return array|null
     */
    public function getParams();

    /**
     * Get context param by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name);

    /**
     * Get product type by name
     *
     * @param string $type
     *
     * @return mixed
     */
    public function retrieveProductTypeByName($type);

    /**
     * Get message template
     *
     * @param string $templateName
     *
     * @return mixed
     */
    public function retrieveMessageTemplate($templateName);
}
