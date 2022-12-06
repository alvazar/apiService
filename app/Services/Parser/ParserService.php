<?php

namespace App\Services\Parser;

use App\Core\Container;
use App\Core\Service;

class ParserService extends Service
{
    protected const SEARCH_TYPE_LIST = [
        'links', 'text', 'images'
    ];

    public function validateRules(): array
    {
        return [
            'url' => ['required', 'strip_tags', 'mb_strtolower', 'regexp/http/i'],
            'searchType' => ['required', function ($value) {
                return in_array($value, self::SEARCH_TYPE_LIST);
            }],
            'searchQuery' => ['strip_tags', 'trim', function ($value) {
                return !empty($value);
            }],
        ];
    }

    public function run(array $params = []): array
    {
        $url = $params['url'];
        $searchType = $params['searchType'];
        $searchQuery = $params['searchQuery'] ?? '';

        $repository = Container::get('HttpRepository');
        $repository->setParam('url', $url);

        $content = $repository->get();

        if ($repository->hasError()) {
            $this->setError($repository->getError());

            return [];
        }

        $findList = [];

        switch ($searchType) {
            case 'links':
                $findList = $this->findLinks($content, $searchQuery);
                break;
            case 'images':
                $findList = $this->findImages($content, $searchQuery);
                break;
            case 'text':
                $findList = $this->findTexts($content, $searchQuery);
                break;
        }
        
        $db = Container::get('DBRepository');

        if ($db->hasError()) {
            $this->setError($db->getError());

            return [];
        }
        
        $db->setParam('table', 'sites_content')
           ->setParam('fields', [
                'url' => $url,
                'searchType' => $searchType,
                'searchQuery' => $searchQuery,
                'quantity' => count($findList),
                'data' => implode("\n", $findList),
                'updated' => date('Y-m-d H:i:s')
           ])
           ->setParam('where', [
                'url' => $url,
                'searchType' => $searchType,
                'searchQuery' => $searchQuery,
           ])
           ;
        
        $db->send();

        return $findList;
    }

    protected function findLinks(string $content, string $searchQuery = ''): array
    {
        $target = !empty($searchQuery)
            ? '.+?' . preg_quote($searchQuery) . '.+?'
            : '.+?';

        $regexp = '/\<a.+?href\=\"(' . $target . ')\"/iu';
        preg_match_all($regexp, $content, $match);

        return $match[1] ?? [];
    }

    protected function findTexts(string $content, string $searchQuery = ''): array
    {
        if (empty($searchQuery)) {
            return [];
        }

        $regexp = '/\W(\w*?' . preg_quote($searchQuery) . '\w*?)\W/iu';
        preg_match_all($regexp, $content, $match);

        return $match[1] ?? [];
    }

    protected function findImages(string $content, string $searchQuery = ''): array
    {
        $target = !empty($searchQuery)
            ? '.+?' . preg_quote($searchQuery) . '.+?'
            : '.+?';

        $regexp = '/\<img.+?src\=\"(' . $target . ')\"/iu';
        preg_match_all($regexp, $content, $match);

        return $match[1] ?? [];
    }
}
