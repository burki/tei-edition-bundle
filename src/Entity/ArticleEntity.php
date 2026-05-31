<?php

// src/Entity/ArticleEntity.php

namespace TeiEditionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'article_entity')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['person' => 'ArticlePerson', 'organization' => 'ArticleOrganization', 'place' => 'ArticlePlace', 'landmark' => 'ArticleLandmark', 'event' => 'ArticleEvent', 'bibitem' => 'ArticleBibitem'])]
abstract class ArticleEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected $id;

    public function setArticle(Article $article)
    {
        if (property_exists($this, 'article')) {
            $this->article = $article;
        }

        return $this;
    }

    public function getArticle()
    {
        return property_exists($this, 'article') ? $this->article : null;
    }

    abstract public function setEntity($entity);

    abstract public function getEntity();
}
