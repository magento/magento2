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
 * @category    Magento
 * @package     Magento_Contacts
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Contacts index controller
 *
 * @category   Magento
 * @package    Magento_Contacts
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Contacts\Controller;

class Index extends \Magento\Core\Controller\Front\Action
{
    const XML_PATH_EMAIL_RECIPIENT  = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'contacts/email/email_template';
    const XML_PATH_ENABLED          = 'contacts/contacts/enabled';

    /**
     * Check is page enabled
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }

    /**
     * Show Contact Us page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('contactForm')
            ->setFormAction($this->_objectManager->create('Magento\Core\Model\Url')->getUrl('*/*/post'));

        $this->_initLayoutMessages('Magento\Customer\Model\Session');
        $this->_initLayoutMessages('Magento\Catalog\Model\Session');
        $this->renderLayout();
    }

    /**
     * Post user question
     *
     * @throws \Exception
     */
    public function postAction()
    {
        if (!$this->getRequest()->isSecure()) {
            $this->_redirect('*/*/');
            return;
        }
        $post = $this->getRequest()->getPost();
        if ($post) {
            $translate = $this->_objectManager->get('Magento\Core\Model\Translate');
            /* @var $translate \Magento\Core\Model\Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new \Magento\Object();
                $postObject->setData($post);

                $error = false;

                if (!\Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!\Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (\Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

                if ($error) {
                    throw new \Exception();
                }
                $mailTemplate = $this->_objectManager->create('Magento\Core\Model\Email\Template');
                /* @var $mailTemplate \Magento\Core\Model\Email\Template */
                $mailTemplate->setDesignConfig(array(
                    'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                    'store' => $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')
                        ->getStore()->getId()
                ))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig(self::XML_PATH_EMAIL_SENDER),
                        $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new \Exception();
                }

                $translate->setTranslateInline(true);

                $this->_objectManager->get('Magento\Customer\Model\Session')->addSuccess(
                    __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (\Exception $e) {
                $translate->setTranslateInline(true);

                $this->_objectManager->get('Magento\Customer\Model\Session')->addError(
                    __('Something went wrong submitting your request.')
                );
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
}
