<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Source\Website;

use Magento\Store\Model\System\Store;

/**
 * Admin OptionHash will include the default store (Admin) with the OptionHash.
 *
 * This class is needed until the layout file supports supplying arguments to an option model.
 */
class AdminOptionHash extends OptionHash
{
    /**
     * @param Store $systemStore
     * @param bool $withDefaultWebsite
     */
    public function __construct(Store $systemStore, $withDefaultWebsite = true)
    {
        parent::__construct($systemStore, $withDefaultWebsite);
    }
}
