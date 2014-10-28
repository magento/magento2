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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleShopping\Helper;

/**
 * Google Product Category helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Category
{
    const CATEGORY_APPAREL = 'Apparel &amp; Accessories';

    const CATEGORY_CLOTHING = 'Apparel &amp; Accessories &gt; Clothing';

    const CATEGORY_SHOES = 'Apparel &amp; Accessories &gt; Shoes';

    const CATEGORY_BOOKS = 'Media &gt; Books';

    const CATEGORY_DVDS = 'Media &gt; DVDs &amp; Videos';

    const CATEGORY_MUSIC = 'Media &gt; Music';

    const CATEGORY_VGAME = 'Software &gt; Video Game Software';

    const CATEGORY_OTHER = 'Other';

    /**
     * Retrieve list of Google Product Categories
     *
     * @param bool $addOther
     * @return string[]
     */
    public function getCategories($addOther = true)
    {
        $categories = array(
            self::CATEGORY_APPAREL,
            self::CATEGORY_CLOTHING,
            self::CATEGORY_SHOES,
            self::CATEGORY_BOOKS,
            self::CATEGORY_DVDS,
            self::CATEGORY_MUSIC,
            self::CATEGORY_VGAME
        );
        if ($addOther) {
            $categories[] = self::CATEGORY_OTHER;
        }
        return $categories;
    }

    /**
     * Get error message for required attributes
     *
     * @return string
     */
    public function getMessage()
    {
        return sprintf(
            __(
                "For information on Google's required attributes for different product categories, please see this link: %s"
            ),
            '<a href="http://www.google.com/support/merchants/bin/answer.py?answer=1344057" target="_blank">' .
            'http://www.google.com/support/merchants/bin/answer.py?answer=1344057' .
            '</a>'
        );
    }
}
