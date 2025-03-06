<?php
// src/Entity/Organization.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // alias for Gedmo extensions annotations

use FS\SolrBundle\Doctrine\Annotation as Solr;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An organization such as a school, NGO, corporation, club, etc.
 *
 * @see http://schema.org/Organization Documentation on Schema.org
 *
 * @Solr\Document(indexHandler="indexHandler")
 * @Solr\SynchronizationFilter(callback="shouldBeIndexed")
 */
#[ORM\Table(name: 'organization')]
#[ORM\Entity]
#[Solr\Document(indexHandler: 'indexHandler')]
#[Solr\SynchronizationFilter(callback: 'shouldBeIndexed')]
class Organization
implements \JsonSerializable, JsonLdSerializable
{
    use AlternateNameTrait, ArticleReferencesTrait;

    static function formatDateIncomplete($dateStr)
    {
        if (preg_match('/^\d{4}$/', $dateStr)) {
            $dateStr .= '-00-00';
        }
        else if (preg_match('/^\d{4}\-\d{2}$/', $dateStr)) {
            $dateStr .= '-00';
        }
        else if (preg_match('/^(\d+)\.(\d+)\.(\d{4})$/', $dateStr, $matches)) {
            $dateStr = join('-', [ $matches[3], $matches[2], $matches[1] ]);
        }

        return $dateStr;
    }

    /**
     * @var int
     *
     * @Solr\Id
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Solr\Id]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $status = 0;

    /**
     * @var string A short description of the item.
     */
    #[ORM\Column(type: 'json', nullable: true)]
    protected $description;

    /**
     * @var string|null The date that this organization was dissolved.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $dissolutionDate;

    /**
     * @var string The date that this organization was founded.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $foundingDate;

    /**
     * @var string|null The name of the item.
     *
     * @Solr\Field(type="string")
     */
    #[Assert\Type(type: 'string')]
    #[ORM\Column(nullable: true)]
    #[Solr\Field(type: 'string')]
    protected $name;

    /**
     * @var string|null URL of the item.
     */
    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    protected $url;

    /**
     * @var Place|null The place where the Organization was founded.
     */
    #[ORM\JoinColumn(name: 'foundingLocation_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \TeiEditionBundle\Entity\Place::class)]
    protected $foundingLocation;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    protected $gnd;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    protected $wikidata;

    #[ORM\OneToMany(targetEntity: \Article::class, mappedBy: 'provider')]
    #[ORM\OrderBy(['dateCreated' => 'ASC', 'name' => 'ASC'])]
    protected $providerOf;

    #[ORM\Column(type: 'json', nullable: true)]
    protected $additional;

    /**
     * @var Organization|null The organization that preceded this on.
     */
    #[ORM\JoinColumn(name: 'precedingId', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: \TeiEditionBundle\Entity\Organization::class, inversedBy: 'succeedingOrganization')]
    protected $precedingOrganization;

    /**
     * @var Organization|null The organization that suceeded this on.
     */
    #[ORM\OneToOne(targetEntity: \TeiEditionBundle\Entity\Organization::class, mappedBy: 'precedingOrganization')]
    protected $succeedingOrganization;

    #[ORM\OneToMany(targetEntity: \ArticleOrganization::class, mappedBy: 'organization', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $articleReferences;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'changed_at', type: 'datetime')]
    protected $changedAt;

    /**
     * @var string|null
     */
    #[Assert\Type(type: 'string')]
    #[ORM\Column(nullable: true)]
    protected $slug;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets status.
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets description.
     *
     * @param array|null $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return array|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gets description in a specific locale.
     *
     * @return string
     */
    public function getDescriptionLocalized($locale)
    {
        if (empty($this->description)) {
            return;
        }

        if (is_array($this->description)) {
            if (array_key_exists($locale, $this->description)) {
                return $this->description[$locale];
            }
        }
        else {
            return $this->description;
        }
    }

    /**
     * Sets dissolutionDate.
     *
     * @param string $dissolutionDate
     *
     * @return $this
     */
    public function setDissolutionDate($dissolutionDate = null)
    {
        $this->dissolutionDate = self::formatDateIncomplete($dissolutionDate);

        return $this;
    }

    /**
     * Gets dissolutionDate.
     *
     * @return string|null
     */
    public function getDissolutionDate()
    {
        return $this->dissolutionDate;
    }

    /**
     * Sets foundingDate.
     *
     * @param string|null $foundingDate
     *
     * @return $this
     */
    public function setFoundingDate($foundingDate = null)
    {
        $this->foundingDate = self::formatDateIncomplete($foundingDate);

        return $this;
    }

    /**
     * Gets foundingDate.
     *
     * @return string|null
     */
    public function getFoundingDate()
    {
        return $this->foundingDate;
    }

    /**
     * Sets name.
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets localized name.
     *
     * @return string
     */
    public function getNameLocalized($locale = 'en')
    {
        if (is_array($this->alternateName)
            && array_key_exists($locale, $this->alternateName)) {
            $name = $this->alternateName[$locale];
        }
        else {
            $name = $this->getName();
        }

        return self::stripAt($name);
    }

    /**
     * Sets url.
     *
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets foundingLocation.
     *
     * @param Place|null $foundingLocation
     *
     * @return $this
     */
    public function setFoundingLocation(?Place $foundingLocation = null)
    {
        $this->foundingLocation = $foundingLocation;

        return $this;
    }

    /**
     * Gets foundingLocation.
     *
     * @return Place|null
     */
    public function getFoundingLocation()
    {
        return $this->foundingLocation;
    }

    /**
     * Sets precedingOrganization.
     *
     * @param Organization|null $precedingOrganization
     *
     * @return $this
     */
    public function setPrecedingOrganization(?Organization $precedingOrganization = null)
    {
        $this->precedingOrganization = $precedingOrganization;

        return $this;
    }

    /**
     * Gets precedingOrganization.
     *
     * @return Organization|null
     */
    public function getPrecedingOrganization()
    {
        return $this->precedingOrganization;
    }

    /**
     * Gets succeedingOrganization.
     *
     * @return Organization|null
     */
    public function getSucceedingOrganization()
    {
        return $this->succeedingOrganization;
    }

    /* override method of
     *   use ArticleReferencesTrait;
     * since we want to avoid duplicates with getProviderOf
     */
    public function getArticleReferences($lang = null, $skipProviderOf = true)
    {
        if (is_null($this->articleReferences)) {
            return [];
        }

        $langCode3 = is_null($lang) ? null : \TeiEditionBundle\Utils\Iso639::code1to3($lang);

        return $this->sortArticleReferences($this->articleReferences->filter(
            function ($entity) use ($langCode3, $skipProviderOf) {
                $ret = 1 == $entity->getArticle()->getStatus()
                     && (is_null($langCode3) || $entity->getArticle()->getLanguage() == $langCode3);

                if ($ret && $skipProviderOf && !is_null($this->providerOf)) {
                    // only return if not in providerOf
                    $ret = !$this->providerOf->contains($entity->getArticle());
                }

                return $ret;
            }
        )->toArray());
    }

    /**
     * Sets gnd.
     *
     * @param string|null $gnd
     *
     * @return $this
     */
    public function setGnd(?string $gnd)
    {
        $this->gnd = $gnd;

        return $this;
    }

    /**
     * Gets gnd.
     *
     * @return string|null
     */
    public function getGnd()
    {
        return $this->gnd;
    }

    /**
     * Sets Wikidata QID.
     *
     * @param string|null $wikidata
     *
     * @return $this
     */
    public function setWikidata($wikidata)
    {
        $this->wikidata = $wikidata;

        return $this;
    }

    /**
     * Gets Wikidata QID.
     *
     * @return string|null
     */
    public function getWikidata()
    {
        return $this->wikidata;
    }


    /**
     * Gets providerOf.
     *
     */
    public function getProviderOf($lang = null)
    {
        if (is_null($this->providerOf)) {
            return $this->providerOf;
        }

        $langCode3 = is_null($lang)
            ? null
            : \TeiEditionBundle\Utils\Iso639::code1to3($lang);

        return $this->providerOf->filter(
            function($entity) use ($langCode3) {
               return 1 == $entity->getStatus()
                && (is_null($langCode3) || $entity->getLanguage() == $langCode3);
            }
        );
    }

    /**
     * Sets additional.
     *
     * @param array $additional
     *
     * @return $this
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Gets additional.
     *
     * @return array|null
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Sets slug.
     *
     * @param string|null $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Gets slug.
     *
     * @return string|null
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gnd' => $this->gnd,
            'url' => $this->url,
        ];
    }

    public function jsonLdSerialize($locale, $omitContext = false, $standalone = false)
    {
        $ret = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'name' => $this->getNameLocalized($locale),
        ];

        if ($omitContext) {
            unset($ret['@context']);
        }

        foreach ([ 'founding', 'dissolution'] as $lifespan) {
            $property = $lifespan . 'Date';

            if (!empty($this->$property)) {
                $ret[$property] = \TeiEditionBundle\Utils\JsonLd::formatDate8601($this->$property);
            }

            if ('founding' == $lifespan) {
                $property = $lifespan . 'Location';
                if (!is_null($this->$property)) {
                    $ret[$property] = $this->$property->jsonLdSerialize($locale, true);
                }
            }
        }

        $description = $this->getDescriptionLocalized($locale);
        if (!empty($description)) {
            $ret['description'] = $description;
        }

        foreach ([ 'url' ] as $property) {
            if (!empty($this->$property)) {
                $ret[$property] = $this->$property;
            }
        }

        if (!empty($this->gnd)) {
            $ret['sameAs'] = 'http://d-nb.info/gnd/' . $this->gnd;
        }

        return $ret;
    }

    // solr-stuff
    public function indexHandler()
    {
        return '*';
    }

    /**
     * Index everything that isn't deleted (no explicit publishing needed)
     *
     * @return boolean
     */
    public function shouldBeIndexed()
    {
        return $this->status >= 0;
    }
}
