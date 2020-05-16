<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Customer
{
    /**
     * @Assert\NotBlank(payload={"error_code"="NOT_EMPTY"})
     * @var string
     */
    private $name;

    /**
     * Here we return two (or more) error codes for a single validation failure.
     *
     * @Assert\Email(payload={"error_code"={"INVALID_EMAIL", "EMAIL_VALIDATION_FAILED"}})
     * @var string
     */
    private $email;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
