<?php

namespace Magento\Payment\Ui\Component\Listing\Column\CcType;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
     */
    protected $salesCollection;

    protected $options;
    
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
    ) {
        $this->salesCollection = $collectionFactory->getReport("sales_order_grid_data_source");
    }

    public function toOptionArray()
    {
        if ($this->options === null) {

            $cctype_collection = $this->salesCollection->setOrder('payment_cctype', 'ASC')->addFieldToSelect('payment_cctype')->distinct(true);
            foreach ($cctype_collection as $order) {
                $ccType = $order->getPaymentCctype();
                $this->options[] = [
                    'value' => $ccType,
                    'label' => $ccType
                ];
            }
        }
        
        return $this->options;
    }

}