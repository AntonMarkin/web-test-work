<?php

namespace App\Service\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRequest
{
    private $validator;
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->populate();
    }
    public function validate()
    {
        $errors = $this->validator->validate($this);

        $messages = ['message' => 'Validation failed', 'errors' => []];

        foreach ($errors as $message) {
            $messages['errors'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        if (count($messages['errors']) > 0) {
            $response = new JsonResponse($messages);
            $response->send();

            exit;
        }
    }
    public function getRequest(): Request
    {
        return Request::createFromGlobals();
    }
    protected function populate(): void
    {
        $request = $this->getRequest();
        switch ($request->getMethod()) {
            case 'POST':
                $params = $request->toArray();
                break;
            default:
                $params = $request->query->all();
                break;
        }
        foreach ($params as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{ $property } = $value;
            }
        }
    }
}
