<?php
// src/Entity/ArticleBibitem.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticleBibitem
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Bibitem::class, inversedBy: 'articleReferences')]
    protected $bibitem;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'bibitemReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->bibitem = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->bibitem;
    }
}
