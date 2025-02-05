<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Mail;

use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateData;
use App\Model\Order\Mail\OrderMail;
use App\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Symfony\Component\Routing\RouterInterface;
use Tests\App\Test\TransactionFunctionalTestCase;
use Twig\Environment;

class OrderMailTest extends TransactionFunctionalTestCase
{
    public function testGetMailTemplateNameByStatus()
    {
        $orderStatus1 = $this->getMockBuilder(OrderStatus::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus1->expects($this->atLeastOnce())->method('getId')->willReturn(1);

        $orderStatus2 = $this->getMockBuilder(OrderStatus::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatus2->expects($this->atLeastOnce())->method('getId')->willReturn(2);

        $mailTempleteName1 = OrderMail::getMailTemplateNameByStatus($orderStatus1);
        $mailTempleteName2 = OrderMail::getMailTemplateNameByStatus($orderStatus2);

        $this->assertNotEmpty($mailTempleteName1);
        $this->assertIsString($mailTempleteName1);

        $this->assertNotEmpty($mailTempleteName2);
        $this->assertIsString($mailTempleteName2);

        $this->assertNotSame($mailTempleteName1, $mailTempleteName2);
    }

    public function testGetMessageByOrder()
    {
        $routerMock = $this->getMockBuilder(RouterInterface::class)
            ->setMethods(['generate'])
            ->getMockForAbstractClass();
        $routerMock->expects($this->any())->method('generate')->willReturn('generatedUrl');

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->setMethods(['getRouter'])
            ->disableOriginalConstructor()
            ->getMock();
        $domainRouterFactoryMock->expects($this->any())->method('getRouter')->willReturn($routerMock);

        $twigMock = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $orderItemPriceCalculationMock = $this->getMockBuilder(
            OrderItemPriceCalculation::class,
        )->disableOriginalConstructor()->getMock();
        $settingMock = $this->getMockBuilder(Setting::class)->disableOriginalConstructor()->getMock();
        $settingMock->expects($this->any())->method('getForDomain')->willReturn('no-reply@shopsys.com');
        $priceExtensionMock = $this->getMockBuilder(PriceExtension::class)->disableOriginalConstructor()->getMock();
        $dateTimeFormatterExtensionMock = $this->getMockBuilder(
            DateTimeFormatterExtension::class,
        )->disableOriginalConstructor()->getMock();
        $orderUrlGeneratorMock = $this->getMockBuilder(
            OrderUrlGenerator::class,
        )->disableOriginalConstructor()->getMock();

        $orderMail = new OrderMail(
            $settingMock,
            $domainRouterFactoryMock,
            $twigMock,
            $orderItemPriceCalculationMock,
            $this->domain,
            $priceExtensionMock,
            $dateTimeFormatterExtensionMock,
            $orderUrlGeneratorMock,
        );

        $order = $this->getReference('order_1');

        $mailTemplateData = new MailTemplateData();
        $mailTemplateData->subject = 'subject';
        $mailTemplateData->body = 'body';
        $mailTemplate = new MailTemplate('templateName', Domain::FIRST_DOMAIN_ID, $mailTemplateData);

        $messageData = $orderMail->createMessage($mailTemplate, $order);

        $this->assertInstanceOf(MessageData::class, $messageData);
        $this->assertSame($mailTemplate->getSubject(), $messageData->subject);
        $this->assertSame($mailTemplate->getBody(), $messageData->body);
    }
}
