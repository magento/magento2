<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class SaveRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency implements HttpPostActionInterface
{
    private const TBL_DIRECTORY_CURRENCY_RATE = 'directory_currency_rate';

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private $conn;
    /** @var \Magento\Framework\Locale\FormatInterface */
    private $format;
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Locale\FormatInterface $format
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resource = $resource;
        $this->conn = $this->resource->getConnection();
        $this->format = $format;
    }

    /**
     * Save rates action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('rate');
        if (is_array($data)) {
            try {
                /* registry for already processed currencies to build cross rates values */
                $currenciesProcessed = [];
                foreach ($data as $currencyCode => $rates) {
                    foreach ($rates as $currencyTo => $value) {
                        /* direct rate */
                        $rateDirect = abs($this->format->getNumber($value));
                        $data[$currencyCode][$currencyTo] = $rateDirect;
                        if ($rateDirect == 0) {
                            $this->messageManager->addWarning(
                                __('Please correct the input data for "%1 => %2" rate.', $currencyCode, $currencyTo)
                            );
                        } else {
                            /* reverse rate */
                            $rateReverse = 1 / $rateDirect;
                            $rateReverse = abs($this->format->getNumber($rateReverse));
                            $data[$currencyTo][$currencyCode] = $rateReverse;

                            /* cross rates for already processed currencies */
                            foreach ($currenciesProcessed as $currencyCross) {
                                $rateBase = $data[$currencyCode][$currencyCross];
                                /* direct cross */
                                $crossDirect = $rateBase / $rateDirect;
                                $crossDirect = abs($this->format->getNumber($crossDirect));
                                $data[$currencyTo][$currencyCross] = $crossDirect;
                                /* reverse cross */
                                $crossReverse = 1 / $crossDirect;
                                $crossReverse = abs($this->format->getNumber($crossReverse));
                                $data[$currencyCross][$currencyTo] = $crossReverse;

                            }
                            $currenciesProcessed[] = $currencyTo;
                        }
                    }
                }
                $this->updateRates($data);
                $this->messageManager->addSuccess(__('All valid rates have been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/*/');
    }

    private function removeOldRates()
    {
        $table = $this->resource->getTableName(self::TBL_DIRECTORY_CURRENCY_RATE);
        $this->conn->delete($table);
    }

    /**
     * Remove existing rates and save new ones.
     *
     * @param $data
     */
    private function updateRates($data)
    {
        /* this is a controller, so we can wrap DB operations with transaction */
        $this->conn->beginTransaction();
        $this->removeOldRates();
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->_objectManager->create(\Magento\Directory\Model\Currency::class);
        $currency->saveRates($data);
        $this->conn->commit();
    }
}
