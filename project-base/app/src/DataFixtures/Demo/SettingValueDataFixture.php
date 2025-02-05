<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Component\Setting\Setting;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const FREE_TRANSPORT_AND_PAYMENT_LIMIT = 10000;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper
     */
    public function __construct(
        private readonly Setting $setting,
        private readonly Domain $domain,
        private readonly PricingSetting $pricingSetting,
        private readonly HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            if ($domainId === 1) {
                $this->pricingSetting->setFreeTransportAndPaymentPriceLimit(
                    $domainId,
                    Money::create(self::FREE_TRANSPORT_AND_PAYMENT_LIMIT),
                );
            }

            /** @var \App\Model\Article\Article $termsAndConditions */
            $termsAndConditions = $this->getReferenceForDomain(
                ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS,
                $domainId,
            );
            $this->setting->setForDomain(
                Setting::TERMS_AND_CONDITIONS_ARTICLE_ID,
                $termsAndConditions->getId(),
                $domainId,
            );

            /** @var \App\Model\Article\Article $privacyPolicy */
            $privacyPolicy = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, $domainId);
            $this->setting->setForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy->getId(), $domainId);

            /** @var \App\Model\Article\Article $cookies */
            $cookies = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_COOKIES, $domainId);
            $this->setting->setForDomain(Setting::COOKIES_ARTICLE_ID, $cookies->getId(), $domainId);

            $personalDataDisplaySiteContent = t(
                'By entering an email below, you can view your personal information that we register in our online store.
                An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page listing all the personal details we have connected to your email address.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT,
                $personalDataDisplaySiteContent,
                $domainId,
            );

            $personalDataExportSiteContent = t(
                'By entering an email below, you can download your personal and other information (for example, order history)
                from our online store. An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data
                registered to given email address on this online store domain.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT,
                $personalDataExportSiteContent,
                $domainId,
            );

            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
            $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, $domainId);
            $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $domainId);

            $this->setting->setForDomain(
                SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE,
                t('Shopsys Platform - the best solution for your eshop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_MAIN_PAGE,
                t('Shopsys Platform - Title page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_ADD_ON,
                t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_ROBOTS_TXT_CONTENT,
                'Disallow: *?filter=',
                $domainId,
            );
            $this->setting->setForDomain(
                Setting::TRANSFER_DAYS_BETWEEN_STOCKS,
                7,
                $domainId,
            );

            $this->setting->setForDomain(
                MailSetting::MAIL_WHITELIST,
                '["/@shopsys\\\\.com$/"]',
                $domainId,
            );

            $this->setDomainDefaultCurrency($domainId);

            if ($this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($locale)) {
                $this->setting->setForDomain(
                    HeurekaSetting::HEUREKA_API_KEY,
                    '96411416349324269511946875061235',
                    $domainId,
                );
            }
        }
        $this->setting->set(BaseSetting::IMAGE_STRUCTURE_MIGRATED_FOR_PROXY, true);
    }

    /**
     * @param int $domainId
     */
    private function setDomainDefaultCurrency(int $domainId): void
    {
        if ($domainId === Domain::SECOND_DOMAIN_ID) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        } else {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        }
        $this->setting->setForDomain(PricingSetting::DEFAULT_DOMAIN_CURRENCY, $defaultCurrency->getId(), $domainId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            ArticleDataFixture::class,
            PricingGroupDataFixture::class,
            CurrencyDataFixture::class,
        ];
    }
}
