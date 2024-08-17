<?php
// src/Entity/ArticleEvent.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticleEvent
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Event::class, inversedBy: 'articleReferences')]
    protected $event;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'eventReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->event = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->event;
    }
}
