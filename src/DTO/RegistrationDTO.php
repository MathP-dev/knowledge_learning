<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDTO
{
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(min: 2, max: 100)]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire. ')]
    #[Assert\Length(min: 2, max:  100)]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email n\'est pas valide.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'
    )]
    public ?string $password = null;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire.')]
    #[Assert\EqualTo(propertyPath: 'password', message: 'Les mots de passe ne correspondent pas.')]
    public ?string $confirmPassword = null;
}