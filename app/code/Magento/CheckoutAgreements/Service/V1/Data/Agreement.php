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
 * Checkout agreement data object.
 *
 * @codeCoverageIgnore
 */
class Agreement extends AbstractExtensibleObject
{
    /**
     * Agreement ID.
     */
    const ID = 'id';

    /**
     * Agreement name.
     */
    const NAME = 'name';

    /**
     * Agreement content.
     */
    const CONTENT = 'content';

    /**
     * Agreement content height. Optional CSS property.
     */
    const CONTENT_HEIGHT = 'content_height';

    /**
     * Agreement checkbox text. Caption of UI component.
     */
    const CHECKBOX_TEXT = 'checkbox_text';

    /**
     * Agreement status.
     */
    const ACTIVE = 'active';

    /**
     * Agreement content type. True is HTML. False is plain text.
     */
    const HTML = 'html';

    /**
     * Returns the agreement ID.
     *
     * @return int Agreement ID.
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Returns the agreement name.
     *
     * @return string Agreement name.
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Returns the agreement content.
     *
     * @return string Agreement content.
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * Returns the agreement content height, which is an optional CSS property.
     *
     * @return string|null Agreement content height. Otherwise, null.
     */
    public function getContentHeight()
    {
        return $this->_get(self::CONTENT_HEIGHT);
    }

    /**
     * Returns the agreement checkbox text.
     *
     * @return string Agreement checkbox text.
     */
    public function getCheckboxText()
    {
        return $this->_get(self::CHECKBOX_TEXT);
    }

    /**
     * Returns the agreement status.
     *
     * @return bool Agreement status.
     */
    public function isActive()
    {
        return $this->_get(self::ACTIVE);
    }

    /**
     * Returns the agreement content type.
     *
     * @return bool * true - HTML.
     * * false - plain text.
     */
    public function isHtml()
    {
        return $this->_get(self::HTML);
    }
}
