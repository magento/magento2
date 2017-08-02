<?php
/**
 * Entity attribute configuration schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Config;

use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Eav\Model\Entity\Attribute\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Schema file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Eav') . '/eav_attributes.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
