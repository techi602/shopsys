<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="availabilities")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation translation(?string $locale = null)
 */
class Availability extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $dispatchTime;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     */
    public function __construct(AvailabilityData $availabilityData)
    {
        $this->translations = new ArrayCollection();
        $this->setData($availabilityData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     */
    public function edit(AvailabilityData $availabilityData)
    {
        $this->setData($availabilityData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     */
    protected function setData(AvailabilityData $availabilityData): void
    {
        $this->setTranslations($availabilityData);
        $this->dispatchTime = $availabilityData->dispatchTime;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     */
    protected function setTranslations(AvailabilityData $availabilityData)
    {
        foreach ($availabilityData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation
     */
    protected function createTranslation()
    {
        return new AvailabilityTranslation();
    }

    /**
     * @return int|null
     */
    public function getDispatchTime()
    {
        return $this->dispatchTime;
    }
}
