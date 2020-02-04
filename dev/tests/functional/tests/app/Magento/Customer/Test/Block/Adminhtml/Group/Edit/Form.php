<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Group\Edit;

use Magento\Mtf\Client\Locator;

/**
 * Customer group edit form.
 */
class Form extends \Magento\Mtf\Block\Form
{
    /**
     * Check if field exists and is disabled.
     *
     * @param string $field
     * @return bool
     * @throws \Exception
     */
    public function isFieldDisabled($field)
    {
        if (!isset($this->mapping[$field])) {
            throw new \Exception("Cannot find field $field. Check for field mapping in " . self::class);
        }
        $disabledField = $this->mapping[$field]['selector'];
        $strategy = isset($this->mapping[$field]['strategy'])
            ? $this->mapping[$field]['strategy']
            : Locator::SELECTOR_CSS;
        return $this->_rootElement->find($disabledField, $strategy)->isDisabled();
    }
}
