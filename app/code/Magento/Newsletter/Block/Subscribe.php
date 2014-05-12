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

/**
 * Newsletter subscribe block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block;

class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * Newsletter session
     *
     * @var \Magento\Newsletter\Model\Session
     */
    protected $_newsletterSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Newsletter\Model\Session $newsletterSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Newsletter\Model\Session $newsletterSession,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_newsletterSession = $newsletterSession;
        $this->_isScopePrivate = true;
    }

    /**
     * Get success message
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->_newsletterSession->getSuccess();
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_newsletterSession->getError();
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', array('_secure' => true));
    }
}
