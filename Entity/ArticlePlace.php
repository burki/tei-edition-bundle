<?php
// src/Entity/ArticlePlace.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticlePlace
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Place::class, inversedBy: 'articleReferences')]
    protected $place;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'placeReferences')]
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
