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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Possible data fields:
 *
 * - subject
 * - to
 * - from
 * - body
 * - template (file name)
 * - module (for template)
 *
 */
namespace Magento\Core\Model;

class Email extends \Magento\Object
{
    protected $_tplVars = array();
    protected $_block;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Layout factory
     *
     * @var \Magento\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @param Store\Config $coreStoreConfig
     * @param \Magento\View\LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\View\LayoutFactory $layoutFactory,
        array $data = array()
    ) {
        $this->_layoutFactory = $layoutFactory;
        $this->_coreStoreConfig = $coreStoreConfig;
        // TODO: move to config
        $this->setFromName('Magento');
        $this->setFromEmail('magento@varien.com');
        $this->setType('text');
        parent::__construct($data);
    }

    public function setTemplateVar($var, $value = null)
    {
        if (is_array($var)) {
            foreach ($var as $index=>$value) {
                $this->_tplVars[$index] = $value;
            }
        }
        else {
            $this->_tplVars[$var] = $value;
        }
        return $this;
    }

    public function getTemplateVars()
    {
        return $this->_tplVars;
    }

    public function getBody()
    {
        $body = $this->getData('body');
        if (empty($body) && $this->getTemplate()) {
            $this->_block = $this->_layoutFactory->create()->createBlock('Magento\Core\Block\Template', 'email')
                ->setArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)
                ->setTemplate($this->getTemplate());
            foreach ($this->getTemplateVars() as $var=>$value) {
                $this->_block->assign($var, $value);
            }
            $this->_block->assign('_type', strtolower($this->getType()))
                ->assign('_section', 'body');
            $body = $this->_block->toHtml();
        }
        return $body;
    }

    public function getSubject()
    {
        $subject = $this->getData('subject');
        if (empty($subject) && $this->_block) {
            $this->_block->assign('_section', 'subject');
            $subject = $this->_block->toHtml();
        }
        return $subject;
    }

    public function send()
    {
        if ($this->_coreStoreConfig->getConfigFlag('system/smtp/disable')) {
            return $this;
        }

        $mail = new \Zend_Mail();

        if (strtolower($this->getType()) == 'html') {
            $mail->setBodyHtml($this->getBody());
        }
        else {
            $mail->setBodyText($this->getBody());
        }

        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail(), $this->getToName())
            ->setSubject($this->getSubject());
        $mail->send();

        return $this;
    }
}
