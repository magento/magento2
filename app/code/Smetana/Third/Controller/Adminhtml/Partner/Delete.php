<?php
namespace Smetana\Third\Controller\Adminhtml\Partner;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Api\PartnerRepositoryInterface;

/**
 * Class delete Partner
 *
 * @package Smetana\Third\Controller\Adminhtml\Partner
 */
class Delete extends Action
{
    /**
     * Partner repository instance
     *
     * @var PartnerRepositoryInterface
     */
    private $partnerRepository;

    /**
     * @param Action\Context $context
     * @param PartnerRepositoryInterface $partnerRepository
     */
    public function __construct(
        Action\Context $context,
        PartnerRepositoryInterface $partnerRepository
    ) {
        $this->partnerRepository = $partnerRepository;
        parent::__construct($context);
    }

    /**
     * Execute delete action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $partnerId = $this->getRequest()->getParam('id');
        if ($partnerId) {
            /** @var PartnerInterface $partner */
            $partner = $this->partnerRepository->getById($partnerId, PartnerInterface::PARTNER_ID);
            if ($partner->getPartnerId()) {
                try {
                    $this->partnerRepository->delete($partner);
                    $this->messageManager->addSuccessMessage('The Partner has been deleted');
                    return $resultRedirect->setPath('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['id' => $partnerId]);
                }
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a Partner to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
