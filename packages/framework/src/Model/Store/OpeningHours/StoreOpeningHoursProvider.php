<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use DateTimeImmutable;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Spatie\OpeningHours\OpeningHours as SpatieOpeningHours;
use Symfony\Contracts\Service\ResetInterface;

class StoreOpeningHoursProvider implements ResetInterface
{
    protected const DAY_NUMBERS_TO_ENGLISH_NAMES_MAP = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    /**
     * @var \Spatie\OpeningHours\OpeningHours[]
     */
    protected array $openingHoursSetting = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHoursDataFactory
     */
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly OpeningHoursDataFactory $openingHoursDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return bool
     */
    public function isOpenNow(Store $store): bool
    {
        $now = new DateTimeImmutable(timezone: $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($store->getDomainId()));

        return $this->getOpeningHoursSetting($store)->isOpenAt($now);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public function getThisWeekOpeningHours(Store $store): array
    {
        $openingHoursData = [];

        foreach (static::DAY_NUMBERS_TO_ENGLISH_NAMES_MAP as $dayName) {
            $openingHoursData[] = $this->getOpeningHoursDataForDayInThisWeek($dayName, $store);
        }

        return $openingHoursData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Spatie\OpeningHours\OpeningHours
     */
    protected function getOpeningHoursSetting(Store $store): SpatieOpeningHours
    {
        $storeId = $store->getId();

        if (array_key_exists($storeId, $this->openingHoursSetting) === false) {
            $this->openingHoursSetting[$storeId] = SpatieOpeningHours::create([
                ...$this->getWeekSetting($store),
                'exceptions' => $this->getExceptions($store),
            ]);
        }

        return $this->openingHoursSetting[$storeId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return array
     */
    protected function getWeekSetting(Store $store): array
    {
        $weekSetting = [];

        foreach ($store->getOpeningHours() as $openingHour) {
            $openingHoursOfDay = [];

            if ($openingHour->getFirstOpeningTime() !== null && $openingHour->getFirstClosingTime() !== null) {
                $openingHoursOfDay[] = $openingHour->getFirstOpeningTime() . '-' . $openingHour->getFirstClosingTime();
            }

            if ($openingHour->getSecondOpeningTime() !== null && $openingHour->getSecondClosingTime() !== null) {
                $openingHoursOfDay[] = $openingHour->getSecondOpeningTime() . '-' . $openingHour->getSecondClosingTime();
            }

            $weekSetting[$this->getEnglishDayNameFromDayNumber($openingHour->getDayOfWeek())] = $openingHoursOfDay;
        }

        return $weekSetting;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return array[][]
     */
    protected function getExceptions(Store $store): array
    {
        $exceptions = [];
        $closedDays = $this->closedDayFacade->getThisWeekClosedDaysNotExcludedForStore(
            $this->domain->getId(),
            $store,
        );

        foreach ($closedDays as $closedDay) {
            $exceptions[$closedDay->getDate()->format('Y-m-d')] = [];
        }

        return $exceptions;
    }

    /**
     * @param string $dayName
     * @return int
     */
    protected function getDayNumberFromEnglishDayName(string $dayName): int
    {
        $dayNumber = array_search($dayName, static::DAY_NUMBERS_TO_ENGLISH_NAMES_MAP, true);

        return $dayNumber !== false ? $dayNumber : throw new InvalidArgumentException(sprintf('Day name "%s" is not valid', $dayName));
    }

    /**
     * @param int $dayNumber
     * @return string
     */
    protected function getEnglishDayNameFromDayNumber(int $dayNumber): string
    {
        return static::DAY_NUMBERS_TO_ENGLISH_NAMES_MAP[$dayNumber] ?? throw new InvalidArgumentException(sprintf('Day number "%s" is not valid. (expected a value in range 1-7)', $dayNumber));
    }

    /**
     * @param string $dayName
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData
     */
    protected function getOpeningHoursDataForDayInThisWeek(string $dayName, Store $store): OpeningHoursData
    {
        $date = new DateTimeImmutable('this ' . $dayName);
        $openingHoursForDay = $this->getOpeningHoursSetting($store)->forDate($date);

        $openingHourData = $this->openingHoursDataFactory->create();
        $openingHourData->dayOfWeek = $this->getDayNumberFromEnglishDayName($dayName);
        $openingRangeNumber = 1;
        /** @var \Spatie\OpeningHours\TimeRange $openingHour */
        foreach ($openingHoursForDay->getIterator() as $openingHour) {
            if ($openingRangeNumber === 1) {
                $openingHourData->firstOpeningTime = $openingHour->start()->format();
                $openingHourData->firstClosingTime = $openingHour->end()->format();
            } elseif ($openingRangeNumber === 2) {
                $openingHourData->secondOpeningTime = $openingHour->start()->format();
                $openingHourData->secondClosingTime = $openingHour->end()->format();
            }
            $openingRangeNumber++;
        }

        return $openingHourData;
    }

    public function reset(): void
    {
        $this->openingHoursSetting = [];
    }
}
