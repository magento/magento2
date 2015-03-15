<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfig;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that store can be localized.
 */
class AssertStoreCanBeLocalized extends AbstractConstraint
{
    /**
     * Assert that locale options can be changed and checks new text on index page.
     *
     * @param SystemConfig $systemConfig
     * @param Store $store
     * @param CmsIndex $cmsIndex
     * @param string $locale
     * @param string $welcomeText
     * @return void
     */
    public function processAssert(SystemConfig $systemConfig, Store $store, CmsIndex $cmsIndex, $locale, $welcomeText)
    {
        // Set locale options
        $systemConfig->open();
        $systemConfig->getPageActions()->selectStore($store->getGroupId() . "/" . $store->getName());
        $configGroup = $systemConfig->getForm()->getGroup('Locale Options');
        $configGroup->open();
        $configGroup->setValue('select-groups-locale-fields-code-value', $locale);
        $systemConfig->getPageActions()->save();
        $systemConfig->getMessagesBlock()->waitSuccessMessage();

        // Check presents income text on index page
        $cmsIndex->open();
        $cmsIndex->getStoreSwitcherBlock()->selectStoreView($store->getName());

        \PHPUnit_Framework_Assert::assertTrue(
            $cmsIndex->getSearchBlock()->isPlaceholderContains($welcomeText),
            "Locale not applied."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store locale has changed successfully.';
    }
}
