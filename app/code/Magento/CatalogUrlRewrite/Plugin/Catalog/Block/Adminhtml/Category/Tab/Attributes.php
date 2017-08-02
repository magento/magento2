<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

/**
 * Class Attributes
 * @since 2.0.0
 */
class Attributes
{
    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     *
     * @return array
     * @since 2.1.0
     */
    public function afterGetAttributesMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        $result
    ) {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $subject->getCurrentCategory();
        if (isset($result['url_key'])) {
            if ($category && $category->getId()) {
                if ($category->getLevel() == 1) {
                    $result['url_key_group']['componentDisabled'] = true;
                } else {
                    $result['url_key_create_redirect']['valueMap']['true'] = $category->getUrlKey();
                    $result['url_key_create_redirect']['value'] = $category->getUrlKey();
                    $result['url_key_create_redirect']['disabled'] = true;
                }
            } else {
                $result['url_key_create_redirect']['visible'] = false;
            }
        }
        return $result;
    }
}
