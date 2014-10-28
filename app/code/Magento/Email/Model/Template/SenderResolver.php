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
namespace Magento\Email\Model\Template;

class SenderResolver implements \Magento\Framework\Mail\Template\SenderResolverInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($sender, $scopeId = null)
    {
        $result = array();

        if (!is_array($sender)) {
            $result['name'] = $this->_scopeConfig->getValue(
                'trans_email/ident_' . $sender . '/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeId
            );
            $result['email'] = $this->_scopeConfig->getValue(
                'trans_email/ident_' . $sender . '/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeId
            );
        } else {
            $result = $sender;
        }

        if (!isset($result['name']) || !isset($result['email'])) {
            throw new \Magento\Framework\Mail\Exception(__('Invalid sender data'));
        }

        return $result;
    }
}
