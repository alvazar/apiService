<?php

namespace App\Controllers;

use App\Core\Container;
use App\Core\Interfaces\ResponseInterface;
use App\Core\Controller;
use App\Core\Response;
use App\Services\ServiceContainer;

class ApiController extends Controller
{
    protected const ERROR_SERVICE_NOT_FOUND = 'Сервис %s не найден';
    protected const ERROR_SERVICE = 'Произошла ошибка: %s';

    public function send(array $params): ResponseInterface
    {
        $serviceName = $params['action'] ?? '';

        $response = new Response();
        $service = ServiceContainer::get($serviceName);

        if (!isset($service)) {
            $response->setError(sprintf(self::ERROR_SERVICE_NOT_FOUND, $serviceName));

            return $response;
        }

        $validator = Container::get('validator');
        $validator->check($service->validateRules(), $params);

        if ($validator->hasError()) {
            $response->setError(implode("\n", $validator->getErrors()));

            return $response;
        }

        $serviceResult = $service->run($params);

        if ($service->hasError()) {
            $response->setError(sprintf(self::ERROR_SERVICE, $service->getError()));

            return $response;
        }

        $response
            ->setData($serviceResult)
            ->setSuccess()
            ;

        return $response;
    }
}
