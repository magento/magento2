<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Service\Config;

use Magento\Framework\Service\Data\MetadataServiceInterface;
use Magento\Framework\Service\Config\Reader as ServiceConfigReader;
use Magento\Framework\Service\Data\AttributeMetadataBuilderInterface;

/**
 * Class which allows to get a metadata of the attributes declared in a config.
 */
class MetadataConfig implements MetadataServiceInterface
{
    /**
     * @var ServiceConfigReader
     */
    private $serviceConfigReader;

    /**
     * @var AttributeMetadataBuilderInterface
     */
    private $attributeMetadataBuilder;

    /**
     * Initialize dependencies.
     *
     * @param ServiceConfigReader $serviceConfigReader
     * @param AttributeMetadataBuilderInterface $attributeMetadataBuilder
     */
    public function __construct(
        ServiceConfigReader $serviceConfigReader,
        AttributeMetadataBuilderInterface $attributeMetadataBuilder
    ) {
        $this->serviceConfigReader = $serviceConfigReader;
        $this->attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $attributes = [];
        if (!is_null($this->attributeMetadataBuilder) && !is_null($dataObjectClassName)) {
            /**
             * Attribute metadata builder and data object class name are expected to be configured
             * via DI using virtual types. If configuration is missing, empty array should be returned.
             */
            $allAttributes = $this->serviceConfigReader->read();
            if (isset($allAttributes[$dataObjectClassName])
                && is_array($allAttributes[$dataObjectClassName])
            ) {
                $attributeCodes = array_keys($allAttributes[$dataObjectClassName]);
                foreach ($attributeCodes as $attributeCode) {
                    $this->attributeMetadataBuilder->setAttributeCode($attributeCode);
                    $attributes[$attributeCode] = $this->attributeMetadataBuilder->create();
                }
            }
        }
        return $attributes;
    }
}
