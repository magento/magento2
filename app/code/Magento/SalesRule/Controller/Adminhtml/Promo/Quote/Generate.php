<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Generate promo quote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generate extends Quote implements HttpPostActionInterface
{
    /**
     * Coupon quantity limit config path
     */
    private const XML_CONFIG_COUPON_QUANTITY_LIMIT = 'promo/auto_generated_coupon_codes/quantity_limit';

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * Generate constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param Date $dateFilter
     * @param ForwardFactory $resultForwardFactory
     * @param CouponGenerator|null $couponGenerator
     * @param PublisherInterface|null $publisher
     * @param CouponGenerationSpecInterfaceFactory|null $generationSpecFactory
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Date $dateFilter,
        ForwardFactory $resultForwardFactory,
        CouponGenerator $couponGenerator = null,
        PublisherInterface $publisher = null,
        CouponGenerationSpecInterfaceFactory $generationSpecFactory = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->couponGenerator = $couponGenerator ?:
            $this->_objectManager->get(CouponGenerator::class);
        $this->messagePublisher = $publisher ?: ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->generationSpecFactory = $generationSpecFactory ?:
            ObjectManager::getInstance()->get(
                CouponGenerationSpecInterfaceFactory::class
            );
        $this->scopeConfig = $scopeConfig ?:
            ObjectManager::getInstance()->get(
                ScopeConfigInterface::class
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
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');

            return;
        }
        $result = [];
        $this->_initRule();

        $rule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } elseif ((int) $rule->getCouponType() !== Rule::COUPON_TYPE_AUTO
            && !$rule->getUseAutoGeneration()) {
            $result['error'] =
                __('The rule coupon settings changed. Please save the rule before using auto-generation.');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                if (!empty($data['to_date'])) {
                    $inputFilter = new \Zend_Filter_Input(['to_date' => $this->_dateFilter], [], $data);
                    $data = $inputFilter->getUnescaped();
                }

                $data['quantity'] = $data['qty'] ?? 0;

                $couponQuantityLimit = (int)$this->scopeConfig->getValue(
                    self::XML_CONFIG_COUPON_QUANTITY_LIMIT,
                    ScopeInterface::SCOPE_STORE
                );
                // @codingStandardsIgnoreStart
                if ($data['quantity'] > 0 && ($data['quantity'] <= $couponQuantityLimit || $couponQuantityLimit === 0)) {
                    $couponSpec = $this->generationSpecFactory->create(['data' => $data]);

                    $this->messagePublisher->publish('sales_rule.codegenerator', $couponSpec);
                    $this->messageManager->addSuccessMessage(
                        __('Message is added to queue, wait to get your coupons soon.')
                    );
                } else {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Coupon quantity should be less than or equal to the coupon quantity in the store configuration.'
                        )
                    );
                }
                // @codingStandardsIgnoreEnd
                $this->_view->getLayout()->initMessages();
                $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (InputException $inputException) {
                $result['error'] = __('Invalid data provided');
            } catch (LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(Data::class)->jsonEncode($result)
        );
    }
}
