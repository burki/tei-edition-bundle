<?php
// src/Entity/ArticlePerson.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticlePerson
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Person::class, inversedBy: 'articleReferences')]
    protected $person;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'personReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->person = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->person;
    }
}
