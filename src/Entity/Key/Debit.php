<?php

declare(strict_types=1);

namespace App\Entity\Key;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Debit extends Transaction
{
    public const OPERATION = "DEB";

    /**
     * @ORM\OneToOne(targetEntity=Transfer::class)
     */
    private ?Transfer $transfer = null;

    public function __construct(Wallet $wallet, int $points, Transfer $transfer)
    {
        parent::__construct($wallet, $points);
        $this->transfer = $transfer;
        $wallet->addTransaction($this);
    }

    public function getTransfer(): Transfer
    {
        return $this->transfer;
    }

    public function getType(): string
    {
        return "Débit";
    }
}
