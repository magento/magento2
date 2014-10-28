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
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

class Attribute extends AbstractPlugin
{
    /**
     * Invalidate indexer on attribute save (searchable flag change)
     *
     * @param \Magento\Catalog\Model\Resource\Attribute $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return \Magento\Catalog\Model\Resource\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\Resource\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $needInvalidation = !$attribute->isObjectNew() && $attribute->dataHasChangedFor('is_searchable');
        $result = $proceed($attribute);
        if ($needInvalidation) {
            $this->getIndexer()->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on searchable attribute delete
     *
     * @param \Magento\Catalog\Model\Resource\Attribute $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return \Magento\Catalog\Model\Resource\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Model\Resource\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $needInvalidation = !$attribute->isObjectNew() && $attribute->getIsSearchable();
        $result = $proceed($attribute);
        if ($needInvalidation) {
            $this->getIndexer()->invalidate();
        }

        return $result;
    }
}
