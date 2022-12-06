<?php

namespace App\Services\Parser;

use App\Core\Container;
use App\Core\Service;

class ContentListService extends Service
{
    protected const SEARCH_TYPE_LIST = [
        'links', 'text', 'images'
    ];

    public function validateRules(): array
    {
        return [
            'url' => ['strip_tags', 'mb_strtolower', 'regexp/http/i'],
            'searchType' => [function ($value) {
                return in_array($value, self::SEARCH_TYPE_LIST);
            }],
            'searchQuery' => ['strip_tags', 'trim'],
        ];
    }

    public function run(array $params = []): array
    {
        $url = $params['url'] ?? null;
        $searchType = $params['searchType'] ?? null;
        $searchQuery = $params['searchQuery'] ?? null;

        $db = Container::get('DBRepository');

        if ($db->hasError()) {
            $this->setError($db->getError());

            return [];
        }
        
        $where = [];

        if (!empty($url)) {
            $where['url'] = $url;
        }

        if (!empty($searchType)) {
            $where['searchType'] = $searchType;
        }

        if (!empty($searchQuery)) {
            $where['searchQuery'] = $searchQuery;
        }

        $db->setParam('table', 'sites_content')
           ->setParam('where', $where);
        
        $db->setParam('orderBy', 'updated DESC');
        
        $itemsList = $db->get();

        return $itemsList;
    }
}
