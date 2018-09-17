<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;

/**
 * Class DescriptorDataBuilder
 */
class DescriptorDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $descriptorKey = 'descriptor';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param Config $config
     * @param SubjectReader|null $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader = null)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader ?: ObjectManager::getInstance()->get(SubjectReader::class);
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $values = $this->config->getDynamicDescriptors($order->getStoreId());
        return !empty($values) ? [self::$descriptorKey => $values] : [];
    }
}
