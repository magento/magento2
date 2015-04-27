<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var Config */
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
     * @param Config $config
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        Repository $assetRepo,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'cc' => [
                    'availableTypes' => $this->getCcAvailableTypes(),
                    'months' => $this->getCcMonths(),
                    'years' => $this->getCcYears(),
                    'hasVerification' => $this->hasVerification(),
                    'hasSsCardType' => $this->hasSsCardType(),
                    'ssStartYears' => $this->getSsStartYears(),
                    'cvvImage' => $this->getCvvImage()
                ]
            ]
        ];
    }

    /**
     * Solo/switch card start years
     *
     * @return array
     */
    protected function getSsStartYears()
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
    protected function getCcAvailableTypes()
    {
        return $this->config->getCcTypes();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->config->getMonths();
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->config->getYears();
    }

    /**
     * Retrieve has verification configuration
     *
     * @return bool
     */
    protected function hasVerification()
    {
        return true;
    }

    /**
     * Whether switch/solo card type available
     *
     * @return bool
     */
    protected function hasSsCardType()
    {
        return false;
    }

    /**
     * Retrieve image content
     *
     * @return string
     */
    protected function getCvvImage()
    {
        $imageUrl = $this->getViewFileUrl('Magento_Checkout::cvv.png');
        return '<img src="' . $imageUrl . '" alt="' . __('Card Verification Number Visual Reference')
            . '" title="' . __('Card Verification Number Visual Reference') . '" />';
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    protected function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

}
