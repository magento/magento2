<?php
namespace Smetana\Third\Controller\Adminhtml\Partner;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Api\PartnerRepositoryInterface;

/**
 * Class edit Partner
 *
 * @package Smetana\Third\Controller\Adminhtml\Partner
 */
class Edit extends Action
{
    /**
     * Registry instance
     *
     * @var Registry
     */
    private $registry;

    /**
     * Result Page Factory instance
     *
     * @var Result\PageFactory
     */
    private $pageFactory;

    /**
     * Partner repository instance
     *
     * @var PartnerRepositoryInterface
     */
    private $partnerRepository;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param Result\PageFactory $pageFactory
     * @param PartnerRepositoryInterface $partnerRepository
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        Result\PageFactory $pageFactory,
        PartnerRepositoryInterface $partnerRepository
    ) {
        $this->registry = $registry;
        $this->pageFactory = $pageFactory;
        $this->partnerRepository = $partnerRepository;
        parent::__construct($context);
    }

    /**
     * Execute edit action
     */
    public function execute(): Result\Page
    {
        $modelId = $this->getRequest()->getParam('id');
        if (null !== $modelId) {
            /** @var PartnerInterface $partnerModel */
            $partnerModel = $this->partnerRepository->getById($modelId, PartnerInterface::PARTNER_ID);
            $this->registry->register('editPartner', $partnerModel);
        }

        /** @var Result\Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Smetana_Third::partner')
            ->getConfig()
            ->getTitle()
            ->prepend(__(isset($partnerModel) ? $partnerModel->getTitle() : ''));

        return $page;
    }
}
