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
 * @package     Magento_Oauth
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OAuth abstract authorization block
 *
 * @category   Magento
 * @package    Magento_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method string getToken()
 * @method \Magento\Oauth\Block\AbstractAuthorizeBase setToken() setToken(string $token)
 * @method boolean getIsSimple()
 * @method \Magento\Oauth\Block\Authorize\Button setIsSimple() setIsSimple(boolean $flag)
 * @method boolean getHasException()
 * @method \Magento\Oauth\Block\AbstractAuthorizeBase setIsException() setHasException(boolean $flag)
 * @method boolean getVerifier()
 * @method \Magento\Oauth\Block\AbstractAuthorizeBase setVerifier() setVerifier(string $verifier)
 * @method boolean getIsLogged()
 * @method \Magento\Oauth\Block\AbstractAuthorizeBase setIsLogged() setIsLogged(boolean $flag)
 */
namespace Magento\Oauth\Block\Authorize;

abstract class AbstractAuthorize extends \Magento\Core\Block\Template
{
    /**
     * Consumer model
     *
     * @var \Magento\Oauth\Model\Consumer
     */
    protected $_consumer;

    /**
     * @var \Magento\Oauth\Model\TokenFactory
     */
    protected $tokenFactory;

    /**
     * @param \Magento\Oauth\Model\TokenFactory $tokenFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Oauth\Model\TokenFactory $tokenFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->tokenFactory = $tokenFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Get consumer instance by token value
     *
     * @return \Magento\Oauth\Model\Consumer
     */
    public function getConsumer()
    {
        if (null === $this->_consumer) {
            /** @var $token \Magento\Oauth\Model\Token */
            $token = $this->tokenFactory->create();
            $token->load($this->getToken(), 'token');
            $this->_consumer = $token->getConsumer();
        }
        return $this->_consumer;
    }

    /**
     * Get absolute path to template
     *
     * Load template from adminhtml/default area flag is_simple is set
     *
     * @return string
     */
    public function getTemplateFile()
    {
        if (!$this->getIsSimple()) {
            return parent::getTemplateFile();
        }

        //load base template from admin area
        $params = array(
            '_relative' => true,
            'area'     => 'adminhtml',
            'package'  => 'default'
        );
        return $this->_viewFileSystem->getFilename($this->getTemplate(), $params);
    }
}
