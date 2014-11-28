<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends \Magento\Customer\Controller\Address
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressDataBuilder $addressDataBuilder
     * @param \Magento\Customer\Api\Data\RegionDataBuilder $regionDataBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param CustomerRepositoryInterface $customerRepository
     * @internal param \Magento\Customer\Helper\Data $customerData
     * @internal param \Magento\Customer\Model\AddressFactory $addressFactory
     * @internal param \Magento\Customer\Model\Address\FormFactory $addressFormFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressDataBuilder $addressDataBuilder,
        \Magento\Customer\Api\Data\RegionDataBuilder $regionDataBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataBuilder,
            $regionDataBuilder,
            $dataProcessor
        );
    }

    /**
     * Customer addresses list
     *
     * @return void
     */
    public function execute()
    {
        $addresses = $this->customerRepository->getById($this->_getSession()->getCustomerId())->getAddresses();
        if (count($addresses)) {
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();

            $block = $this->_view->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_buildUrl('*/*/new'));
        }
    }
}
