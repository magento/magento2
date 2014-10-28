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
namespace Magento\Backend\Model\Config\Structure\Element;

class Section extends AbstractComposite
{
    /**
     * Authorization service
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Iterator $childrenIterator
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        Iterator $childrenIterator,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        parent::__construct($storeManager, $childrenIterator);
        $this->_authorization = $authorization;
    }

    /**
     * Retrieve section header css
     *
     * @return string
     */
    public function getHeaderCss()
    {
        return isset($this->_data['header_css']) ? $this->_data['header_css'] : '';
    }

    /**
     * Check whether section is allowed for current user
     *
     * @return bool
     */
    public function isAllowed()
    {
        return isset($this->_data['resource']) ? $this->_authorization->isAllowed($this->_data['resource']) : false;
    }

    /**
     * Check whether element should be displayed
     *
     * @return bool
     */
    public function isVisible()
    {
        if (!$this->isAllowed()) {
            return false;
        }
        return parent::isVisible();
    }
}
