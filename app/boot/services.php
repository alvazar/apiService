<?php

use App\Services\Parser\ContentListService;
use App\Services\Parser\ParserService;
use App\Services\ServiceContainer;

// register services
ServiceContainer::add('parser', ParserService::class);
ServiceContainer::add('getParserContentList', ContentListService::class);
