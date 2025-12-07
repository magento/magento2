<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Date;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DateTest to test Magento\Backend\Block\Widget\Grid\Column\Filter\Date
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DateTest extends TestCase
{
    use MockCreationTrait;

    /** @var Date */
    protected $model;

    /** @var Random|MockObject */
    protected $mathRandomMock;

    /** @var ResolverInterface|MockObject */
    protected $localeResolverMock;

    /** @var DateTimeFormatterInterface|MockObject */
    protected $dateTimeFormatterMock;

    /** @var Column|MockObject */
    protected $columnMock;

    /** @var TimezoneInterface|MockObject */
    protected $localeDateMock;

    /** @var Escaper|MockObject */
    private $escaperMock;

    /** @var Context|MockObject */
    private $contextMock;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Repository|MockObject
     */
    private $repositoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->mathRandomMock = $this->createPartialMock(
            Random::class,
            ['getUniqueHash']
        );

        $this->localeResolverMock = $this->createMock(ResolverInterface::class);

        $this->dateTimeFormatterMock = $this->createMock(DateTimeFormatterInterface::class);

        $this->columnMock = $this->createPartialMockWithReflection(
            Column::class,
            ['getTimezone', 'getHtmlId', 'getId']
        );

        $this->localeDateMock = $this->createMock(TimezoneInterface::class);

        $this->escaperMock = $this->createMock(Escaper::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        $this->contextMock->expects($this->once())->method('getLocaleDate')->willReturn($this->localeDateMock);

        $this->request = $this->createMock(Http::class);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->repositoryMock = $this->createPartialMock(
            Repository::class,
            ['getUrlWithParams']
        );

        $this->contextMock->expects($this->once())
            ->method('getAssetRepository')
            ->willReturn($this->repositoryMock);

        $this->model = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'mathRandom' => $this->mathRandomMock,
                'localeResolver' => $this->localeResolverMock,
                'dateTimeFormatter' => $this->dateTimeFormatterMock,
                'localeDate' => $this->localeDateMock,
                'context' => $this->contextMock,
            ]
        );
        $this->model->setColumn($this->columnMock);
    }

    public function testGetHtmlSuccessfulTimestamp()
    {
        $uniqueHash = 'H@$H';
        $id = 3;
        $format = 'mm/dd/yyyy';
        $yesterday = new \DateTime();
        $yesterday->add(\DateInterval::createFromDateString('yesterday'));
        $tomorrow = new \DateTime();
        $tomorrow->add(\DateInterval::createFromDateString('tomorrow'));
        $value = [
            'locale' => 'en_US',
            'from' => $yesterday->getTimestamp(),
            'to' => $tomorrow->getTimestamp()
        ];
        $params = ['_secure' => false];
        $fileId = 'Magento_Theme::calendar.png';
        $fileUrl = 'file url';

        $this->repositoryMock->expects($this->once())
            ->method('getUrlWithParams')
            ->with($fileId, $params)
            ->willReturn($fileUrl);

        $this->mathRandomMock->expects($this->any())->method('getUniqueHash')->willReturn($uniqueHash);
        $this->columnMock->expects($this->once())->method('getHtmlId')->willReturn($id);
        $this->localeDateMock->expects($this->any())->method('getDateFormat')->willReturn($format);
        $this->columnMock->expects($this->any())->method('getTimezone')->willReturn(false);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en_US');
        $this->model->setColumn($this->columnMock);
        $this->model->setValue($value);

        $output = $this->model->getHtml();
        $this->assertStringContainsString(
            'id="' . $uniqueHash . '_from" value="' . $yesterday->getTimestamp(),
            $output
        );
        $this->assertStringContainsString(
            'id="' . $uniqueHash . '_to" value="' . $tomorrow->getTimestamp(),
            $output
        );
    }

    public function testGetEscapedValueEscapeString()
    {
        $value = "\"><img src=x onerror=alert(2) />";
        $array = [
            'orig_from' => $value,
            'from' => $value,
        ];
        $this->model->setValue($array);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($value);
        $this->model->getEscapedValue('from');
    }
}
