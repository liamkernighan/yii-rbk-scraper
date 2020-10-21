<?php

namespace app\helpers;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;

class RbkPostsFinder
{
    const HYPERLINK_PATTERN = '%<a[^>]+?class=["]?news-feed__item.+?(?=>)>(.+?)</a>%s';
    const RBK_URL = 'https://rbk.ru';

    public $interval_millis = 500;


    public function withPauseMillis($interval_millis)
    {
        $this->interval_millis = $interval_millis;
        return $this;
    }

    /**
     * @param string $path
     * @param string $base_uri
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function fetchDataFromWeb(string $base_uri = '', string $path = "")
    {
        $client = new Client(['base_uri' => $base_uri, 'verify' => false ]);
        $response = $client->request('GET', $path);
        return $response->getBody()->getContents();
    }

    /**
     * @param int $count
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getHtmlPosts()
    {
        $entire_html = $this->fetchDataFromWeb(self::RBK_URL);
        preg_match_all(self::HYPERLINK_PATTERN, $entire_html, $matches);
        return $matches[0];
    }

    public function getArrayOfStructuredPosts(int $count = 15)
    {
        $html_posts = array_slice($this->getHtmlPosts(), 0, $count);


        $posts = [];
        foreach ($html_posts as $html_post) {

            $post = new \stdClass();
            preg_match('/href="([^"]+)?"/s', $html_post, $matches);
            $post->hyperlink = $matches[1];
            preg_match('%<span[^>]+?class=["]?news-feed__item__title.+?(?=>)>(.+?)</span>%s', $html_post, $matches);
            $title = strip_tags($matches[1]);
            $title = trim(preg_replace('/\s\s+/', ' ', $title));
            $post->title = $title;
            array_push($posts, $post);

            $this->appendContentToPost($post->hyperlink,$post);
        }

        return $posts;
    }

    private function appendContentToPost(string $hyperlink, &$post)
    {
        $post->content = "";
        $post->img_path = "";

        $single_post_html = $this->fetchDataFromWeb($hyperlink);

        $dom = new DomDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($single_post_html);
        libxml_use_internal_errors($internalErrors);
        $finder = new DomXPath($dom);
        $classname="article__content";
        $content_array = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

        if ($content_array->count() === 0) {
            $post->hash = sha1($post->title);
            return;
        }

        $post->content = $dom->saveHTML($content_array[0]);
        $post->hash = sha1($post->title . $post->content);

        $classname = 'article__main-image__image';
        $image_array = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        if ($image_array->count() > 0) {
            $post->img_path = $image_array[0]->getAttribute('src');
        }
    }
}