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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Attribute;

class Config extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = "eav_attributes"
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Retrieve list of locked fields for attribute
     *
     * @param AbstractAttribute $attribute
     * @return array
     */
    public function getLockedFields(AbstractAttribute $attribute)
    {
        $allFields = $this->get(
            $attribute->getEntityType()->getEntityTypeCode() . '/attributes/' . $attribute->getAttributeCode()
        );

        if (!is_array($allFields)) {
            return array();
        }
        $lockedFields = array();
        foreach (array_keys($allFields) as $fieldCode) {
            $lockedFields[$fieldCode] = $fieldCode;
        }

        return $lockedFields;
    }

    /**
     * Retrieve attributes list with config for entity
     *
     * @param string $entityCode
     * @return array
     */
    public function getEntityAttributesLockedFields($entityCode)
    {
        $lockedFields = array();

        $entityAttributes = $this->get($entityCode . '/attributes');
        foreach ($entityAttributes as $attributeCode => $attributeData) {
            foreach ($attributeData as $attributeField) {
                if ($attributeField['locked']) {
                    $lockedFields[$attributeCode][] = $attributeField['code'];
                }
            }
        }

        return $lockedFields;
    }
}
