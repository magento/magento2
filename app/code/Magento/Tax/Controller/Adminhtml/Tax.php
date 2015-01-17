<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

use Magento\Framework\Exception\InputException;

/**
 * Adminhtml common tax class controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassDataBuilder
     */
    protected $taxClassDataBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService
     * @param \Magento\Tax\Api\Data\TaxClassDataBuilder taxClassDataBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService,
        \Magento\Tax\Api\Data\TaxClassDataBuilder $taxClassBuilder
    ) {
        $this->taxClassRepository = $taxClassService;
        $this->taxClassDataBuilder = $taxClassBuilder;
        parent::__construct($context);
    }

    /**
     * Validate/Filter Tax Class Name
     *
     * @param string $className
     * @return string processed class name
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function _processClassName($className)
    {
        $className = trim($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($className));
        if ($className == '') {
            throw new InputException('Invalid name of tax class specified.');
        }
        return $className;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
    }
}
