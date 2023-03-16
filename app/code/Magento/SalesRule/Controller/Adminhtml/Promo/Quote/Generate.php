<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Exception;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterInput;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote as AdminhtmlPromoQuote;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Model\Quote\GetCouponCodeLengthInterface;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule as ModelRule;
use Psr\Log\LoggerInterface;

/**
 * Generate promo quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generate extends AdminhtmlPromoQuote implements HttpPostActionInterface
{
    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * Generate constructor.
     * @param ActionContext $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param DateFilter $dateFilter
     * @param CouponGenerator|null $couponGenerator
     * @param PublisherInterface|null $publisher
     * @param CouponGenerationSpecInterfaceFactory|null $generationSpecFactory
     * @param GetCouponCodeLengthInterface|null $getCouponCodeLength
     */
    public function __construct(
        ActionContext $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        DateFilter $dateFilter,
        private ?CouponGenerator $couponGenerator = null,
        PublisherInterface $publisher = null,
        private ?CouponGenerationSpecInterfaceFactory $generationSpecFactory = null,
        private ?GetCouponCodeLengthInterface $getCouponCodeLength = null
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->couponGenerator = $couponGenerator ?:
            $this->_objectManager->get(CouponGenerator::class);
        $this->messagePublisher = $publisher ?: ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->generationSpecFactory = $generationSpecFactory ?:
            ObjectManager::getInstance()->get(
                CouponGenerationSpecInterfaceFactory::class
            );
        $this->getCouponCodeLength = $getCouponCodeLength ?:
            ObjectManager::getInstance()->get(
                GetCouponCodeLengthInterface::class
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

        $rule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        $data = $this->getRequest()->getParams();

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } elseif ((int) $rule->getCouponType() !== ModelRule::COUPON_TYPE_AUTO
            && !$rule->getUseAutoGeneration()) {
            $result['error'] =
                __('The rule coupon settings changed. Please save the rule before using auto-generation.');
        } elseif ((int)$data['length'] !==
            $this->getCouponCodeLength->fetchCouponCodeLength(
                $data
            )
        ) {
            try {
                $minimumLength = $this->getCouponCodeLength->fetchCouponCodeLength(
                    $data
                );
                $quantity = $data['qty'];
                $this->messageManager->addErrorMessage(
                    __(
                        'When coupon quantity exceeds %1, the coupon code length must be minimum %2',
                        $quantity,
                        $minimumLength
                    )
                );
                $this->_view->getLayout()->initMessages();
                $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (Exception $e) {
                $result['error'] = __(
                    'Something went wrong while validating coupon code length. Please review the log and try again.'
                );
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }
        } else {
            try {
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
            } catch (InputException $inputException) {
                $result['error'] = __('Invalid data provided');
            } catch (LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
        );
    }
}
