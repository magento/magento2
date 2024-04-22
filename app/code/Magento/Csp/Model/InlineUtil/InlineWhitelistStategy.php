<?php
declare(strict_types=1);

namespace Magento\Csp\Model\InlineUtil;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Csp\Model\Collector\ConfigCollector;
use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Helper\SecureHtmlRender\TagData;
use Magento\Framework\View\LayoutInterface;

class InlineWhitelistStategy
{
    private ?CspNonceProvider $nonceProvider;
    private LayoutInterface $generalLayout;
    private DynamicCollector $dynamicCollector;
    private ?ConfigCollector $configCollector;
    private HashGenerator $hashGenerator;

    /**
     * @param LayoutInterface $generalLayout
     * @param DynamicCollector $dynamicCollector
     * @param HashGenerator $hashGenerator
     * @param ConfigCollector|null $configCollector
     * @param CspNonceProvider|null $nonceProvider
     */
    public function __construct(
        LayoutInterface   $generalLayout,
        DynamicCollector  $dynamicCollector,
        HashGenerator     $hashGenerator,
        ?ConfigCollector  $configCollector = null,
        ?CspNonceProvider $nonceProvider = null
    ) {
        $this->nonceProvider = $nonceProvider ?? ObjectManager::getInstance()->get(CspNonceProvider::class);
        $this->configCollector = $configCollector ?? ObjectManager::getInstance()->get(ConfigCollector::class);
        $this->generalLayout = $generalLayout;
        $this->dynamicCollector = $dynamicCollector;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * Return a tag that is safe to be used
     *
     * @param TagData $tagData
     * @param string $policyId
     * @return TagData
     * @throws LocalizedException
     */
    public function getSanitisedTag(TagData $tagData, string $policyId): TagData
    {
        if (!$tagData->getContent()
            || !$this->isInlineDisabled($policyId)
        ) {
            return $tagData;
        }

        if ($tagData->getTag() === 'script'
            && !$this->generalLayout->isCacheable()
        ) {
            $nonce = $this->nonceProvider->generateNonce();
            $tagAttributes = $tagData->getAttributes();
            $tagAttributes['nonce'] = $nonce;
            $newTagData = new TagData(
                $tagData->getTag(),
                $tagAttributes,
                $tagData->getContent(),
                $tagData->isTextContent()
            );

            $tagData = $newTagData;
        } else {
            $this->dynamicCollector->add(
                new FetchPolicy(
                    $policyId,
                    false,
                    [],
                    [],
                    false,
                    false,
                    false,
                    [],
                    $this->hashGenerator->generateHashValue($tagData->getContent())
                )
            );
        }

        return $tagData;
    }

    /**
     * Check if inline sources are prohibited.
     *
     * @param string $policyId
     * @return bool
     */
    private function isInlineDisabled(string $policyId): bool
    {
        foreach ($this->configCollector->collect() as $policy) {
            if ($policy->getId() === $policyId && $policy instanceof FetchPolicy) {
                return !$policy->isInlineAllowed();
            }
        }

        return false;
    }
}
