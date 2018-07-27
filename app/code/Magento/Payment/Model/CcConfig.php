<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Config as PaymentConfig;

class CcConfig
{
    /** @var PaymentConfig */
    protected $config;

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
     * @param PaymentConfig $paymentConfig
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        Repository $assetRepo,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->config = $paymentConfig;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * Solo/switch card start years
     *
     * @return array
     * @deprecated unused
     */
    public function getSsStartYears()
    {
        $years = [];
        $first = date("Y");

        for ($index = 5; $index >= 0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        return $years;
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return $this->config->getCcTypes();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        return $this->config->getMonths();
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        return $this->config->getYears();
    }

    /**
     * Retrieve has verification configuration
     *
     * @return bool
     */
    public function hasVerification()
    {
        return true;
    }

    /**
     * Whether switch/solo card type available
     *
     * @return bool
     * @deprecated unused
     */
    public function hasSsCardType()
    {
        return false;
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    public function getCvvImageUrl()
    {
        return $this->getViewFileUrl('Magento_Checkout::cvv.png');
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
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
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     * @return \Magento\Framework\View\Asset\File
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        return $this->assetRepo->createAsset($fileId, $params);
    }
}
