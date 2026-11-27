<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Plugin\Webapi;

use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\OptionsPreloader;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Prepare attribute options only when serializing attribute list route responses.
 */
class AttributeOptionsOutput
{
    /**
     * Route services and methods returning attribute lists.
     *
     * @var array<string, string>
     */
    private const SERVICES = [
        ProductAttributeManagementInterface::class => 'getAttributes',
        ProductAttributeRepositoryInterface::class => 'getList',
    ];

    /**
     * Initialize the output options preloader.
     *
     * @param OptionsPreloader $optionsPreloader
     */
    public function __construct(private readonly OptionsPreloader $optionsPreloader)
    {
    }

    /**
     * Replace only attribute list output data with copies carrying prepared options.
     *
     * @param ServiceOutputProcessor $subject
     * @param mixed $data
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(
        ServiceOutputProcessor $subject,
        mixed $data,
        string $serviceClassName,
        string $serviceMethodName
    ): array {
        if ((self::SERVICES[$serviceClassName] ?? null) !== $serviceMethodName) {
            return [$data, $serviceClassName, $serviceMethodName];
        }
        if (is_array($data)) {
            $data = $this->optionsPreloader->preload($data);
        } elseif ($data instanceof SearchResultsInterface) {
            $data = clone $data;
            $data->setItems($this->optionsPreloader->preload($data->getItems()));
        }
        return [$data, $serviceClassName, $serviceMethodName];
    }
}
