<?php
// src/Entity/ArticleOrganization.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticleOrganization
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Organization::class, inversedBy: 'articleReferences')]
    protected $organization;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'organizationReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->organization = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->organization;
    }
}
