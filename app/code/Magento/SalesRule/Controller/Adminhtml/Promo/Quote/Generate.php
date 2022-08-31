<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Filter\FilterInput;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;

/**
 * Generate promo quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generate extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote implements HttpPostActionInterface
{
    /**
     * @var CouponGenerator
     */
    private $couponGenerator;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    private $generationSpecFactory;

    /**
     * Generate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param CouponGenerator|null $couponGenerator
     * @param PublisherInterface|null $publisher
     * @param CouponGenerationSpecInterfaceFactory|null $generationSpecFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        CouponGenerator $couponGenerator = null,
        PublisherInterface $publisher = null,
        CouponGenerationSpecInterfaceFactory $generationSpecFactory = null
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->couponGenerator = $couponGenerator ?:
            $this->_objectManager->get(CouponGenerator::class);
        $this->messagePublisher = $publisher ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->generationSpecFactory = $generationSpecFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                CouponGenerationSpecInterfaceFactory::class
            );
    }

    /**
     * Generate Coupons action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        $result = [];
        $this->_initRule();

        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } elseif ((int) $rule->getCouponType() !== \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO
            && !$rule->getUseAutoGeneration()) {
            $result['error'] =
                __('The rule coupon settings changed. Please save the rule before using auto-generation.');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                if (!empty($data['to_date'])) {
                    $inputFilter = new FilterInput(['to_date' => $this->_dateFilter], [], $data);
                    $data = $inputFilter->getUnescaped();
                }

                $data['quantity'] = isset($data['qty']) ? $data['qty'] : null;

                $couponSpec = $this->generationSpecFactory->create(['data' => $data]);

                $this->messagePublisher->publish('sales_rule.codegenerator', $couponSpec);
                $this->messageManager->addSuccessMessage(
                    __('Message is added to queue, wait to get your coupons soon')
                );
                $this->_view->getLayout()->initMessages();
                $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (\Magento\Framework\Exception\InputException $inputException) {
                $result['error'] = __('Invalid data provided');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
