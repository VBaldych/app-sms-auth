<?php

declare(strict_types=1);

namespace App\Request;

use App\Constraint\PhoneNumber;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SmsRequest
{
    #[NotBlank()]
    #[Type('string')]
    #[PhoneNumber()]
    public ?string $phone = null;

    public function __construct(
        protected ValidatorInterface $validator,
        protected RequestStack       $requestStack,
    ) {
        $this->populate();
        $this->validate();
    }

    protected function populate(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        // Check if request format is not valid
        if ($request->getContentTypeFormat() != 'json') {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid request format: expecting 'application/json' in 'Content-Type'");
        }
        
        $reflection = new \ReflectionClass($this);

        foreach ($request->toArray() as $property => $value) {
            if (property_exists($this, $property)) {
                $reflectionProperty = $reflection->getProperty($property);
                $reflectionProperty->setValue($this, $value);
            }
        }
    }

    protected function validate(): void
    {
        $violations = $this->validator->validate($this);
        if (count($violations) < 1) {
            return;
        }

        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, json_encode($errors));
    }
}