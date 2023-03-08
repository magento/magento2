<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\TaxClassRepositoryInterface;

/**
 * Adminhtml common tax class controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Tax extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @var TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @param Context $context
     * @param TaxClassRepositoryInterface $taxClassService
     * @param TaxClassInterfaceFactory $taxClassDataObjectFactory
     */
    public function __construct(
        Context $context,
        TaxClassRepositoryInterface $taxClassService,
        protected readonly TaxClassInterfaceFactory $taxClassDataObjectFactory
    ) {
        $this->taxClassRepository = $taxClassService;
        parent::__construct($context);
    }

    /**
     * Validate/Filter Tax Class Name
     *
     * @param string $className
     * @return string processed class name
     * @throws InputException
     */
    protected function _processClassName($className)
    {
        $className = trim($className);
        if ($className == '') {
            throw new InputException(__('Invalid name of tax class specified.'));
        }
        return $className;
    }
}
