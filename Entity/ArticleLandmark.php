<?php
// src/Entity/ArticleLandmark.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ArticleLandmark
extends ArticleEntity
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Landmark::class, inversedBy: 'articleReferences')]
    protected $landmark;

    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Article::class, inversedBy: 'landmarkReferences')]
    protected $article;

    public function setEntity($entity)
    {
        $this->landmark = $entity;
    }

    public function getEntity()
    {
        return $this->landmark;
    }
}
