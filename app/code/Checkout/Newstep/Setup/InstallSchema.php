<?php
namespace Checkout\Newstep\Setup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
  public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
{
/*customersetupfactory instead of eavsetupfactory */
$customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
$customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
    $attributeSetId = $customerEntity->getDefaultAttributeSetId();
/** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
/* create customer phone number attribute */
$customerSetup->addAttribute(Customer::ENTITY,'additional_name',	[
    'type'         => 'varchar', // attribute with varchar type
    'label'        => 'Additional Name',
    'input'        => 'text',  // attribute input field is text
    'required'     => false,  // field is not required
    'visible'      => true,  
    'user_defined' => true,
    'position'     => 999,
    'sort_order'  => 999,
    'system'       => 0,
    'is_used_in_grid' => 1,   //setting grid options
    'is_visible_in_grid' => 1,
    'is_filterable_in_grid' => 1,
    'is_searchable_in_grid' => 1,
    ]
    );
$customerSetup->addAttribute(Customer::ENTITY,'additional_phone_number',	[
'type'         => 'varchar', 
'label'        => 'Additional phone number',
'input'        => 'text',  // attribute input field is text
'required'     => false,  // field is not required
'visible'      => true,  
'user_defined' => true,
'position'     => 999,
'sort_order'  => 999,
'system'       => 0,
'is_used_in_grid' => 1,   //setting grid options
'is_visible_in_grid' => 1,
'is_filterable_in_grid' => 1,
'is_searchable_in_grid' => 1,
]
);
$sampleAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'additional_name')
->addData(
[
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_edit','customer_account_create'],
        ]
// more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
);
$sampleAttribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'additional_phone_number')
->addData(
[
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer','customer_account_edit','customer_account_create'],
        ]
// more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
);
$sampleAttribute->save();
}
}