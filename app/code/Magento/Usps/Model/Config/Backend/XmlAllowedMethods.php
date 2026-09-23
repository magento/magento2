<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 *
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Backend;

/**
 * Backend model for XML API allowed methods - preserves value when REST API is selected
 */
class XmlAllowedMethods extends AllowedMethods
{
    /**
     * @var string
     */
    protected string $applicableUspsType = 'USPS_XML';
}
