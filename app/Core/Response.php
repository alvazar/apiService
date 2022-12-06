<?php

namespace App\Core;

use App\Core\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    protected array $response = [
        'data' => [],
        'status' => '',
        'message' => ''
    ];

    public function setData(array $data = []): ResponseInterface
    {
        $this->response['data'] = $data;

        return $this;
    }

    public function setSuccess(string $message = ''): ResponseInterface
    {
        $this->response['status'] = 'success';
        $this->response['message'] = $message;

        return $this;
    }

    public function setError(string $message = ''): ResponseInterface
    {
        $this->response['status'] = 'error';
        $this->response['message'] = $message;

        return $this;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getJson(): string
    {
        return json_encode(
            $this->response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }
}
