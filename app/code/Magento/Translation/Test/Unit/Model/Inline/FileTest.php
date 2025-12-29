<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Inline;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Translation\Model\Inline\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $model;

    /**
     * @var ResourceInterface|MockObject
     */
    private $translateResourceMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var Json
     */
    private $jsonSerializer;

    protected function setUp(): void
    {
        $this->translateResourceMock = $this->createMock(ResourceInterface::class);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        $this->jsonSerializer = new Json();

        $this->model = new File(
            $this->translateResourceMock,
            $this->localeResolverMock,
            $this->jsonSerializer
        );
    }

    public function testGetTranslationFileContent()
    {
        $translations = ['string' => 'translatedString'];

        $this->localeResolverMock->expects($this->atLeastOnce())->method('getLocale')->willReturn('en_US');
        $this->translateResourceMock->expects($this->atLeastOnce())->method('getTranslationArray')
            ->willReturn($translations);

        $this->assertEquals(
            $this->jsonSerializer->serialize($translations),
            $this->model->getTranslationFileContent()
        );
    }
}
