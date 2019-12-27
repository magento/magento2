<?php
namespace Smetana\Third\Controller\Adminhtml\Partner;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Smetana\Third\Api\Data;
use Smetana\Third\Api\PartnerRepositoryInterface;

/**
 * Class save Partner
 *
 * @package Smetana\Third\Controller\Adminhtml\Partner
 */
class Save extends Action
{
    /**
     * Post data to save
     *
     * @var array
     */
    private $postValue;

    /**
     * Partner repository instance
     *
     * @var PartnerRepositoryInterface
     */
    private $partnerRepository;

    /**
     * Partner model factory
     *
     * @var Data\PartnerInterfaceFactory
     */
    private $partnerFactory;

    /**
     * @param Action\Context $context
     * @param PartnerRepositoryInterface $partnerRepository
     * @param Data\PartnerInterfaceFactory $partnerFactory
     */
    public function __construct(
        Action\Context $context,
        PartnerRepositoryInterface $partnerRepository,
        Data\PartnerInterfaceFactory $partnerFactory
    ) {
        $this->partnerRepository = $partnerRepository;
        $this->partnerFactory = $partnerFactory;
        parent::__construct($context);
    }

    /**
     * Execute save action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->postValue = $this->getRequest()->getPostValue();
        /** @var Data\PartnerInterface $partnerModel */
        $model = $this->getPreparedModel();

        try {
            $model->setData($this->postValue);
            $this->partnerRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You successfully saved %1 Partner', $model->getPartnerName()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'id' => $model->getPartnerId(),
                        '_current' => true
                    ]
                );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get model to save data
     *
     * @return Data\PartnerInterface
     */
    private function getPreparedModel(): Data\PartnerInterface
    {
        $modelId = $this->postValue[Data\PartnerInterface::PARTNER_ID] ?? $this->postValue['id'] ?? null;
        $partnerModel = $this->partnerFactory->create();
        if ($modelId) {
            /** @var Data\PartnerInterface $partnerModel */
            $partnerModel->load($modelId);
        }

        return $partnerModel;
    }
}
