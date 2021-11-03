<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\CustomerData;

use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Messages section
 */
class Messages implements SectionSourceInterface
{
    /**
     * Manager messages
     *
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * Constructor
     *
     * @param MessageManager $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param RequestInterface $request
     * @param Config $appConfig
     * @param Synchronizer $synchronizer
     */
    public function __construct(
        MessageManager $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        ?RequestInterface $request = null,
        ?Config $appConfig = null,
        ?Synchronizer $synchronizer = null
    ) {
        $this->messageManager = $messageManager;
        $this->interpretationStrategy = $interpretationStrategy;
        $this->request = $request ?: ObjectManager::getInstance()->get(RequestInterface::class);
        $this->appConfig = $appConfig ?: ObjectManager::getInstance()->get(Config::class);
        $this->synchronizer = $synchronizer ?: ObjectManager::getInstance()->get(Synchronizer::class);
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $forceNewSectionTimestampFlg = $this->sectionTimestampFlag();

        $messages = $this->messageManager->getMessages($forceNewSectionTimestampFlg);
        $messageResponse = array_reduce(
            $messages->getItems(),
            function (array $result, MessageInterface $message) {
                $result[] = [
                    'type' => $message->getType(),
                    'text' => $this->interpretationStrategy->interpret($message)
                ];
                return $result;
            },
            []
        );
        return [
            'messages' => $messageResponse
        ];
    }

    /**
     * Verify flag value for synchronizing product actions with backend or not.
     *
     * @return boolean
     */
    private function sectionTimestampFlag(): bool
    {
        $forceNewSectionTimestampFlg = true;

        if ((bool) $this->appConfig->getValue($this->synchronizer::ALLOW_SYNC_WITH_BACKEND_PATH)) {
            $sections = $this->request->getParam('sections');
            $sectionNames = explode(",", $sections);
            if (!empty($sections) && in_array('cart', $sectionNames)) {
                $forceNewSectionTimestampFlg = false;
                $forceNewSectionTimestamp = $this->request->getParam('force_new_section_timestamp')
                    ?? $this->request->getParam('force_new_section_timestamp');

                if ('true' === $forceNewSectionTimestamp) {
                    $forceNewSectionTimestampFlg = true;
                }
            }
        }
        return $forceNewSectionTimestampFlg;
    }
}
