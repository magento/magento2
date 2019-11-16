<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Address;

use Magento\Customer\Model\AddressFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\AddressRepository;

/**
 * Class for common code for buttons on the create/edit address form
 */
class GenericButton
{
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Address
     */
    private $addressResourceModel;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @param AddressFactory $addressFactory
     * @param UrlInterface $urlBuilder
     * @param Address $addressResourceModel
     * @param RequestInterface $request
     * @param AddressRepository $addressRepository
     */
    public function __construct(
        AddressFactory $addressFactory,
        UrlInterface $urlBuilder,
        Address $addressResourceModel,
        RequestInterface $request,
        AddressRepository $addressRepository
    ) {
        $this->addressFactory = $addressFactory;
        $this->urlBuilder = $urlBuilder;
        $this->addressResourceModel = $addressResourceModel;
        $this->request = $request;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Return address Id.
     *
     * @return int|null
     */
    public function getAddressId()
    {
        $address = $this->addressFactory->create();

        $entityId = $this->request->getParam('entity_id');
        $this->addressResourceModel->load(
            $address,
            $entityId
        );

        return $address->getEntityId() ?: null;
    }

    /**
     * Get customer id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerId()
    {
        $addressId = $this->request->getParam('entity_id');

        $address = $this->addressRepository->getById($addressId);

        return $address->getCustomerId() ?: null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
