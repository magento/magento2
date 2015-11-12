<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportProductSaved
 */
class ReportProductSaved
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory
     */
    protected $systemFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\NewRelicReporting\Model\SystemFactory $systemFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        Config $config,
        \Magento\NewRelicReporting\Model\SystemFactory $systemFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->config = $config;
        $this->systemFactory = $systemFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->dateTime = $dateTime;
    }

    /**
     * Reports any products created or updated to the database reporting_system_updates table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\NewRelicReporting\Model\Observer\ReportProductSaved
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getEvent()->getProduct();

            $jsonData = [
                'name' => $product->getName(),
            ];

            if ($product->isObjectNew()) {
                $jsonData['status'] = 'created';
            } else {
                $jsonData['id'] = $product->getId();
                $jsonData['status'] = 'updated';
            }

            $modelData = [
                'type' => Config::PRODUCT_CHANGE,
                'action' => $this->jsonEncoder->encode($jsonData),
                'updated_at' => $this->dateTime->formatDate(true)
            ];

            /** @var \Magento\NewRelicReporting\Model\System $systemModel */
            $systemModel = $this->systemFactory->create();
            $systemModel->setData($modelData);
            $systemModel->save();
        }

        return $this;
    }
}
