<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Address extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private CountryInformationAcquirerInterface $countryInfo;

    /**
     * @var array
     */
    private array $countryNameCache = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CountryInformationAcquirerInterface $countryInfo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CountryInformationAcquirerInterface $countryInfo,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->countryInfo = $countryInfo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $field = $this->getData('name');
                $value = (string)($item[$field] ?? '');
                $value = $this->replaceTrailingCountryCodeWithName($value);
                $item[$field] = nl2br($this->escaper->escapeHtml($value));
            }
        }

        return $dataSource;
    }

    /**
     * Replace country code with name
     *
     * @param string $address
     * @return string
     */
    private function replaceTrailingCountryCodeWithName(string $address): string
    {
        $parts = array_map('trim', explode(',', $address));
        if (count($parts) < 2) {
            return $address;
        }

        $countryCode = strtoupper((string)end($parts));
        if (!preg_match('/^[A-Z]{2}$/', $countryCode)) {
            return $address;
        }

        if (!isset($this->countryNameCache[$countryCode])) {
            try {
                $info = $this->countryInfo->getCountryInfo($countryCode);
                $name = $info->getFullNameLocale() ?: $info->getFullNameEnglish();
                $this->countryNameCache[$countryCode] = $name ?: $countryCode;
            } catch (NoSuchEntityException $e) {
                $this->countryNameCache[$countryCode] = $countryCode;
            }
        }

        $parts[count($parts) - 1] = $this->countryNameCache[$countryCode];
        return implode(', ', $parts);
    }
}
