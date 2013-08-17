<?php
/**
 * Creates a block given failed registration
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Registration_Failed extends Mage_Backend_Block_Template
{
    /** @var  Mage_Backend_Model_Session */
    protected $_session;

    /**
     * @param Mage_Backend_Model_Session $session
     * @param Mage_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Model_Session $session,
        Mage_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_session = $session;
    }

    /**
     * Get error message produced on failure
     *
     * @return string The error message produced upon failure
     */
    public function getSessionError()
    {
        return $this->_session->getMessages(true)
            ->getLastAddedMessage()
            ->toString();
    }
}
