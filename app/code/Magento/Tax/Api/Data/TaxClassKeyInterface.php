<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TaxClassKeyInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TYPE = 'type';

    const KEY_VALUE = 'value';
    /**#@-*/

    /**#@+
     * Constants defined for type of tax class key
     */
    const TYPE_ID = 'id';

    const TYPE_NAME = 'name';
    /**#@-*/

    /**
     * Get type of tax class key
     *
     * @return string
     */
    public function getType();

    /**
     * Get value of tax class key
     *
     * @return string
     */
    public function getValue();
}
