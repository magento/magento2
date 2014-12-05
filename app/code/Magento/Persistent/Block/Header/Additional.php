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
namespace Magento\Persistent\Block\Header;

/**
 * Remember Me block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Additional extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Persistent\Helper\Session $persistentSessionHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Persistent\Helper\Session $persistentSessionHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_customerViewHelper = $customerViewHelper;
        $this->_persistentSessionHelper = $persistentSessionHelper;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve unset cookie link
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('persistent/index/unsetCookie');
    }

    /**
     * Render additional header html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $persistentName = $this->_escaper->escapeHtml(
            $this->_customerViewHelper->getCustomerName(
                $this->customerRepository->getById($this->_persistentSessionHelper->getSession()->getCustomerId())
            )
        );
        return '<span><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml(__('(Not %1?)', $persistentName))
        . '</a></span>';
    }
}
