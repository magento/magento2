<?php

namespace Liip\CustomerHierarchy\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        $customerTypeAttributeCode = 'type';

        $customerSetup->addAttribute(Customer::ENTITY, $customerTypeAttributeCode,  array(
            'type'           => 'static',
            'label'          => 'Type',
            'input'          => 'select',
            'source'         => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            'required'       => false,
            'sort_order'     => 110,
            'visible'        => true,
            'system'         => false,
            'validate_rules' => 'a:0:{}',
            'position'       => 110,
            'admin_checkout' => 1,
            'option'         => ['values' => ['Private', 'Company']],

        ));

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $customerTypeAttributeCode);

        $attribute->setData('used_in_forms', ['adminhtml_customer', 'adminhtml_checkout'])
            ->setData('is_used_for_customer_segment', true)
            ->setData('is_user_defined', 1);
        $attribute->save();
        $setup->endSetup();
    }
}
