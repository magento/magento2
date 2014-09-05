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
namespace Magento\CheckoutAgreements\Service\V1\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class Agreement extends AbstractExtensibleObject
{
    const ID = 'id';
    const NAME = 'name';
    const CONTENT = 'content';
    const CONTENT_HEIGHT = 'content_height';
    const CHECKBOX_TEXT = 'checkbox_text';
    const ACTIVE = 'active';
    const HTML = 'html';

    /**
     * Retrieve agreement ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Retrieve agreement name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Retrieve agreement content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * Retrieve agreement content height (optional CSS property)
     *
     * @return string|null
     */
    public function getContentHeight()
    {
        return $this->_get(self::CONTENT_HEIGHT);
    }

    /**
     * Retrieve agreement checkbox text
     *
     * @return string
     */
    public function getCheckboxText()
    {
        return $this->_get(self::CHECKBOX_TEXT);
    }

    /**
     * Retrieve agreement status
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->_get(self::ACTIVE);
    }

    /**
     * Retrieve agreement content type
     *
     * @return bool
     */
    public function isHtml()
    {
        return $this->_get(self::HTML);
    }
}
