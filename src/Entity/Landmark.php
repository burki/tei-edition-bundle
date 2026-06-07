<?php

// src/Entity/Landmark.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An historical landmark or building.
 *
 * @see https://schema.org/LandmarksOrHistoricalBuildings on Schema.org
 */
#[ORM\Table(name: 'landmark')]
#[ORM\Entity]
class Landmark extends PlaceBase
{
    use ArticleReferencesTrait;

    /**
     * @var string|null The slug within dasjuedischehamburg.de, for example 'max-mustermann' for https://www.dasjuedischehamburg.de/personen/max-mustermann
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $djh;

    /**
     * @var ArticleLandmark[]|null References to articles mentioning this landmark.
     */
    #[ORM\OneToMany(targetEntity: ArticleLandmark::class, mappedBy: 'landmark', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $articleReferences;

    /**
     * Sets djh.
     *
     * @param string|null $djh
     *
     * @return $this
     */
    public function setDjh($djh)
    {
        $this->djh = $djh;

        return $this;
    }

    /**
     * Gets djh.
     *
     * @return string|null
     */
    public function getDjh()
    {
        return $this->djh;
    }

    public function getDefaultZoomlevel(): int
    {
        return 12;
    }

    /*
     * Overridden to get more concrete type
     */
    protected function getSchemaType(): string
    {
        return 'LandmarksOrHistoricalBuildings';
    }
}
