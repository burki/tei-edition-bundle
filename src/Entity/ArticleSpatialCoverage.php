<?php

// src/Entity/ArticleSpatialCoverage.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticleSpatialCoverage extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'articleCoveredReferences')]
    protected $place;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'spatialCoverageReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->place = $entity;
    }

    public function getEntity()
    {
        return $this->place;
    }
}
