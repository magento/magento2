<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\TransparentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class IframeConfigProvider
 * @package Magento\Payment\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * Default implementation of config provider for iframe integrations.
 * Use this class for virtual types declaration.
 * Extends from this class only in case of urgency.
 *
 * @api
 * @since 100.0.2
 */
class IframeConfigProvider implements ConfigProviderInterface
{
    /**
     * 30 sec
     */
    const TIMEOUT_TIME = 30000;

    /**
     * Default length of Cc year field
     */
    const DEFAULT_YEAR_LENGTH = 2;

    /**
     * Checkout identifier for transparent iframe payments
     */
    const CHECKOUT_IDENTIFIER = 'checkout_flow';

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $methodCode;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $method;

    /**
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param string $methodCode
     */
    public function __construct(
        Repository $assetRepo,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        LoggerInterface $logger,
        PaymentHelper $paymentHelper,
        $methodCode
    ) {
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->methodCode = $methodCode;
        $this->method = $paymentHelper->getMethodInstance($methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'iframe' => [
                    'timeoutTime' => [$this->methodCode => self::TIMEOUT_TIME],
                    'dateDelim' => [$this->methodCode => $this->getDateDelim()],
                    'cardFieldsMap' => [$this->methodCode => $this->getCardFieldsMap()],
                    'source' =>  [$this->methodCode => $this->getViewFileUrl('blank.html')],
                    'controllerName' => [$this->methodCode => self::CHECKOUT_IDENTIFIER],
                    'cgiUrl' => [$this->methodCode => $this->getCgiUrl()],
                    'placeOrderUrl' => [$this->methodCode => $this->getPlaceOrderUrl()],
                    'saveOrderUrl' => [$this->methodCode => $this->getSaveOrderUrl()],
                    'expireYearLength' => [$this->methodCode => $this->getExpireDateYearLength()]
                ]
            ]
        ];
    }

    /**
     * Get delimiter for date
     *
     * @return string
     */
    protected function getDateDelim()
    {
        $result = '';
        if ($this->method->isAvailable()) {
            $configData = $this->getMethodConfigData('date_delim');
            if ($configData !== null) {
                $result = $configData;
            }
        }

        return  $result;
    }

    /**
     * Returns Cc expire year length
     *
     * @return int
     */
    protected function getExpireDateYearLength()
    {
         return (int)$this->getMethodConfigData('cc_year_length') ?: self::DEFAULT_YEAR_LENGTH;
    }

    /**
     * Get map of cc_code, cc_num, cc_expdate for gateway
     * Returns json formatted string
     *
     * @return string
     */
    protected function getCardFieldsMap()
    {
        $result = [];
        if ($this->method->isAvailable()) {
            $configData = $this->getMethodConfigData('ccfields');
            $keys = ['cccvv', 'ccexpdate', 'ccnum'];
            $result = array_combine($keys, explode(',', $configData));
        }

        return $result;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string[]
     */
    protected function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

    /**
     * Retrieve place order url on front
     *
     * @return string
     */
    protected function getPlaceOrderUrl()
    {
        return $this->urlBuilder->getUrl(
            $this->getMethodConfigData('place_order_url'),
            [
                '_secure' => $this->request->isSecure()
            ]
        );
    }

    /**
     * Retrieve save order url on front
     *
     * @return string
     */
    protected function getSaveOrderUrl()
    {
        return $this->urlBuilder->getUrl('checkout/onepage/saveOrder', ['_secure' => $this->request->isSecure()]);
    }

    /**
     * Retrieve gateway url
     *
     * @return string
     */
    protected function getCgiUrl()
    {
        return (bool)$this->getMethodConfigData('sandbox_flag')
            ? $this->getMethodConfigData('cgi_url_test_mode')
            : $this->getMethodConfigData('cgi_url');
    }

    /**
     * Retrieve config data value by field name
     *
     * @param string $fieldName
     * @return mixed
     */
    protected function getMethodConfigData($fieldName)
    {
        if ($this->method instanceof TransparentInterface) {
            return $this->method->getConfigInterface()->getValue($fieldName);
        }
        return $this->method->getConfigData($fieldName);
    }
}
