<?php

namespace Trinityrank\LaravelSchemaOrgBuilder;

use DateTime;
use Spatie\SchemaOrg\Graph;
use Spatie\SchemaOrg\Schema;

class SchemaOrgBuilder
{
    public function getSchemaOrg($entity, $node_properties, $config = []) {
        $graph = new Graph();

        foreach($node_properties as $property) {
            $this->{'get'.$property}($graph, $entity, $config);
        }
        
        return $graph->toScript();
    }

    private function getOrganization(Graph $graph, $entity, $config = []) {
        $graph->organization()
            ->identifier(url('/').'#/schema/organization/1')
            ->description(config('main.seo.home.meta_description'))
            ->image(asset(config('schema-org-builder.general.logo')))
            ->foundingDate((new DateTime('2019-07-15'))->format('Y-m-d'))
            ->legalName(config('schema-org-builder.general.name'))
            ->name(config('schema-org-builder.general.name'))
            ->brand(config('schema-org-builder.general.name'))
            ->email(config('main.mail_address'))
            ->sameAs(config('schema-org-builder.sameAs'))
            ->slogan(config('schema-org-builder.slogan'))
            ->url(url('/'));
    }

    private function getWebSite(Graph $graph, $entity, $config = []) {
        $graph->webSite()
            ->identifier(url('/').'#/schema/website/1')
            ->copyrightHolder($graph->organization()->referenced()->toArray())
            ->description(config('main.seo.home.meta_description'))
            ->inLanguage(config('schema-org-builder.general.inLanguage'))
            ->name(config('schema-org-builder.general.name'))
            ->publisher($graph->organization()->referenced()->toArray())
            ->url(url('/'));
    }

    private function getWebPage(Graph $graph, $entity, $config = []) {
        $this->getImageObject($graph, $entity, $config);
        $graph->webPage()
            ->identifier(url()->current())
            ->about($graph->organization()->referenced()->toArray())
            ->datePublished((new DateTime($entity['created_at']))->format('Y-m-d'))
            ->dateModified((new DateTime($entity['updated_at']))->format('Y-m-d'))
            ->description($config['seo']->meta_description)
            ->name($config['seo']->meta_title)
            ->inLanguage(config('schema-org-builder.general.inLanguage'))
            ->url(url()->current())
            ->isPartOf($graph->webSite()->referenced()->toArray())
            ->potentialAction(Schema::readAction()->target(url()->current()))
            ->primaryImageOfPage($graph->imageObject($entity['media'][0]['collection_name'])->referenced()->toArray());

        if(!empty($entity['user'])) {
            $this->getPerson($graph, $entity['user']);
            $graph->webPage()->author($graph->person()->referenced()->toArray());
        }
        if(!empty($config['seo']->meta_keywords) && array_filter($config['seo']->meta_keywords)) {
            $graph->webPage()->keywords($config['seo']->meta_keywords);
        }

        $this->getBreadcrumbs($graph, $entity, $config);
        $this->getFAQPage($graph, $entity, $config);
    }

    private function getArticle(Graph $graph, $entity, $config = []) {
        $type = 'article';
        if((class_exists('\App\Articles\Types\News')) && ($entity instanceof \App\Articles\Types\News)) {
            $type = 'newsArticle';
        }
        
        $graph->{$type}()
            ->identifier($graph->webPage()['url'].'#/schema/article/'.$entity['id'])
            ->headline($entity['title'])
            ->description($config['seo']->meta_description)
            ->inLanguage(config('schema-org-builder.general.inLanguage'))
            ->isPartOf($graph->webPage()->referenced()->toArray())
            ->mainEntityOfPage($graph->webPage()->referenced()->toArray())
            ->datePublished((new DateTime($entity['created_at']))->format('Y-m-d'))
            ->dateModified((new DateTime($entity['updated_at']))->format('Y-m-d'))
            ->publisher($graph->organization()->referenced()->toArray());
    }

    private function getPerson(Graph $graph, $entity, $config = []) {
        $this->getImageObject($graph, $entity, $config);
        $graph->person()
            ->identifier(url('/').'#/schema/person/'.$entity['id'])
            ->name($entity['name']);

        if(!empty($entity['media'][0]['collection_name'])) {
            $graph->person()->image($graph->imageObject($entity['media'][0]['collection_name'])->referenced()->toArray());
        }
    }

    private function getReview(Graph $graph, $entity, $config = []) {
        $review_rating = null;
        $strenghts = $weaknesses = [];

        foreach($entity->decorators as $decorator) {
            if(!in_array($decorator['layout'], config('schema-org-builder.review.relevant_decorators'))) continue;
            if(!empty($decorator['data']['elements'][0]['rating'])) {
                $review_rating = $decorator['data']['elements'][0]['rating'];
            }
            if(!empty($decorator['data']['elements'][0]['strenghts'])) {
                $position = 1;
                foreach($decorator['data']['elements'][0]['strenghts'] as $strenght) {
                    $strenghts[] = Schema::listItem()
                        ->position($position)
                        ->description($strenght['strenght']);
                    $position++;
                }
            }
            if(!empty($decorator['data']['elements'][0]['weaknesses'])) {
                $position = 1;
                foreach($decorator['data']['elements'][0]['weaknesses'] as $weakness) {
                    $weaknesses[] = Schema::listItem()
                        ->position($position)
                        ->description($weakness['weakness']);
                    $position++;
                }
            }
        }

        $graph->review()
            ->identifier(url('/').'#/schema/review/'.$entity['id'])
            ->name($entity['title'])
            ->reviewRating($review_rating)
            ->reviewBody($entity['short_description'])
            ->positiveNotes(Schema::itemList()->itemListElement($strenghts))
            ->negativeNotes(Schema::itemList()->itemListElement($weaknesses));

        $graph->webPage()->review($graph->review()->referenced()->toArray());
    }

    private function getBreadcrumbs(Graph $graph, $entity, $config = []) {
        if(!array_key_exists('breadcrumbs', $config)) return;
        $graph->breadcrumbList()
            ->identifier(url('/').'#/schema/breadcrumb/'.(!empty($entity->id) ? $entity->id : rand()));
        
        $position = 1;
        $list_items = [];
        array_unshift($config['breadcrumbs'], ['name' => 'Home', 'link' => url('/').'/']);
        $counter = count($config['breadcrumbs']);
        foreach($config['breadcrumbs'] as $breadcrumb) {
            $item = Schema::listItem()
                ->position($position)
                ->name($breadcrumb['name']);
            if($counter != $position && isset($breadcrumb['link'])) {
                $item->item($breadcrumb['link']);
            }
            $list_items[] = $item;
            $position++;
        }
        $graph->breadcrumbList()
            ->itemListElement($list_items);

        if(array_key_exists('Spatie\SchemaOrg\WebPage', $graph->getNodes())) {
            $graph->webPage()->breadcrumb($graph->breadcrumbList()->referenced()->toArray());
        }
        if(array_key_exists('Spatie\SchemaOrg\CollectionPage', $graph->getNodes())) {
            $graph->collectionPage()->breadcrumb($graph->breadcrumbList()->referenced()->toArray());
        }
    }

    private function getFAQPage(Graph $graph, $entity, $config = []) {
        $faqs = [];
        foreach($entity->decorators as $decorator) {
            if($decorator['layout'] != 'faq-section') continue;
            $faqs = $decorator['data'];
            break;
        }
        if(empty($faqs)) return;

        $graph->fAQPage()
            ->identifier(url()->current().'#/schema/faqpage/'.$entity->id)
            ->isPartOf($graph->webPage()->referenced()->toArray())
            ->name($faqs['title'] ?: 'FAQ')
            ->mainEntity(
                array_map(function($faq) {
                    return Schema::question()
                        ->name($faq['question'])
                        ->acceptedAnswer(
                            Schema::answer()
                                ->text($faq['answer'])
                        );
                }, $faqs['elements'])
            );
    }

    private function getImageObject(Graph $graph, $entity, $config = []) {
        if(empty($entity['media'][0]['collection_name'])) return;
        $media = $entity->getFirstMedia($entity['media'][0]['collection_name']);
        $media['url'] = $entity->getFirstMediaUrl($entity['media'][0]['collection_name']);

        $graph->imageObject($media['collection_name'])
            ->identifier(url('/').'#/schema/image/'.$media['id'])
            ->url($media['url'])
            ->contentUrl($media['url']);

        if(!empty($media['name'])) {
            $graph->imageObject($media['collection_name'])
                ->caption($media['name']);
        }
    }

    private function getCollectionPage(Graph $graph, $entity, $config = []) {
        $graph->collectionPage()
            ->identifier(url()->current())
            ->about($graph->organization()->referenced()->toArray())
            ->description($config['seo']->meta_description)
            ->name($config['seo']->meta_title)
            ->inLanguage(config('schema-org-builder.general.inLanguage'))
            ->url(url()->current())
            ->isPartOf($graph->webSite()->referenced()->toArray())
            ->potentialAction(Schema::readAction()->target(url()->current()));

        $this->getBreadcrumbs($graph, $entity, $config);
    }

    private function getMoneyPage(Graph $graph, $entity, $config = []) {
        foreach($entity->decorators as $decorator) {
            if(strpos($decorator['layout'], 'table-section') === false) continue;
            Schema::itemList()->identifier($decorator['layout']);
            $position = 1;
            $list_items = [];
            foreach($decorator['data']['elements'] as $element) {
                $list_item = Schema::listItem();
                $list_item->position($position);
                if($element['name']) $list_item->name($element['title']);
                if($element['image']) $list_item->image($element['image']);
                if($element['cta_url']) $list_item->url($element['cta_url']);
                $list_items[] = $list_item;
                $position++;
            }
            $graph->itemList($decorator['layout'])
                ->name($decorator['data']['table_title'])
                ->mainEntityOfPage($graph->webPage()->referenced()->toArray())
                ->itemListElement($list_items);
        }
    }
}
