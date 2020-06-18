<?php


namespace Magento\CustomerGraphQl\Model\Resolver;


use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateCustomerEmail implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;
    /**
     * @var UpdateCustomerAccount
     */
    private $updateCustomerAccount;
    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @param GetCustomer $getCustomer
     * @param UpdateCustomerAccount $updateCustomerAccount
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        GetCustomer $getCustomer,
        UpdateCustomerAccount $updateCustomerAccount,
        ExtractCustomerData $extractCustomerData
    ) {
        $this->getCustomer = $getCustomer;
        $this->updateCustomerAccount = $updateCustomerAccount;
        $this->extractCustomerData = $extractCustomerData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var \Magento\GraphQl\Model\Query\ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (empty($args['email']) || empty($args['password'])) {
            throw new GraphQlInputException(__('"email" and "password" values should be specified'));
        }

        $customer = $this->getCustomer->execute($context);
        $this->updateCustomerAccount->execute(
            $customer,
            ['email' => $args['email'], 'password' => $args['password']],
            $context->getExtensionAttributes()->getStore()
        );

        $data = $this->extractCustomerData->execute($customer);

        return ['customer' => $data];
    }
}
