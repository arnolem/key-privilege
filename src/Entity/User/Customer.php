<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Company\Client;
use App\Repository\User\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer extends User
{
    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="customers")
     */
    private ?Client $client = null;

    public function getRole(): string
    {
        return "ROLE_CUSTOMER";
    }

    public function getRoleName(): string
    {
        return "Utilisateur";
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}
