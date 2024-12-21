<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert; // Ajoutez cette ligne
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Ajout de la contrainte Email et NotBlank
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message: "Please enter a valid email address.")]
    #[Assert\NotBlank(message: "Email cannot be blank.")]
    private ?string $email = null;

    #[ORM\Column(type: "json")]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    // Ajout des contraintes NotBlank pour First Name et Last Name
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "First name cannot be blank.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Last name cannot be blank.")]
    private ?string $lastName = null;

    // Ajout de la contrainte NotBlank et Validation sur le format du numéro de téléphone
    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]{1,4}?[-. ]?(\(?\d{1,3}?\)?[-. ]?\d{1,4}[-. ]?\d{1,4}[-. ]?\d{1,9})$/",
        message: "Please enter a valid phone number."
    )]
    #[Assert\NotBlank(message: "Phone number cannot be blank.")]
    private ?string $phoneNumber = null;

    // Getters and Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantir que chaque utilisateur a au moins ROLE_USER
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles); // Élimine les doublons
    }

    public function setRoles(array $roles): static
    {
        // Ajouter ROLE_USER si ce n'est pas déjà présent
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        
        $this->roles = array_unique($roles);  // Élimine les doublons pour éviter des rôles répétés
        
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    // Getter and Setter for FirstName, LastName, and PhoneNumber

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
