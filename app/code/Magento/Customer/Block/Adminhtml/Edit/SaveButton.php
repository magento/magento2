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
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Ui\Component\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var CustomerAccountServiceInterface
     */
    protected $customerAccountService;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CustomerAccountServiceInterface $customerAccountService
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerAccountServiceInterface $customerAccountService
    ) {
        parent::__construct($context, $registry);
        $this->customerAccountService = $customerAccountService;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $canModify = !$customerId || $this->customerAccountService->canModify($this->getCustomerId());
        $data = [];
        if ($canModify) {
            $data = [
                'label' => __('Save Customer'),
                'class' => 'save primary',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save')),
                    'form-role' => 'save'
                ),
                'sort_order' => 90
            ];
        }
        return $data;
    }
}
