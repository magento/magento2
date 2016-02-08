<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

/**
 * Class Attributes
 */
class Attributes
{
    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     *
     * @return array
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
                    $result['url_key']['arguments']['data']['config']['visible'] = false;
                    $result['url_key_create_redirect']['arguments']['data']['config']['visible'] = false;
                    $result['url_key_group']['arguments']['data']['disabled'] = true;
                } else {
                    $result['url_key_create_redirect']['arguments']['data']['config']['value'] = $category->getUrlKey();
                    $result['url_key_create_redirect']['arguments']['data']['config']['disabled'] = true;
                }
            } else {
                $result['url_key_create_redirect']['arguments']['data']['config']['visible'] = false;
            }
        }
        return $result;
    }
}
