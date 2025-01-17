<?php

declare(strict_types=1);

namespace App\Form\Client\Access;

use App\Entity\Company\Client;
use App\Entity\User\Customer;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Repository\Company\ClientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Manager|SalesPerson $employee */
        $employee = $options["employee"];

        $clientOptions = [];

        if ($employee instanceof Manager && $employee->getMembers()->count() > 1) {
            $clientOptions["group_by"] = fn (Client $client) => $client->getMember()->getName();
        }

        $builder
            ->add("firstName", TextType::class, [
                "label" => "Prénom :",
                "empty_data" => ""
            ])
            ->add("lastName", TextType::class, [
                "label" => "Nom :",
                "empty_data" => ""
            ])
            ->add("email", EmailType::class, [
                "label" => "Adresse email :",
                "empty_data" => ""
            ])
            ->add("client", EntityType::class, $clientOptions + [
                "label" => "Client :",
                "class" => Client::class,
                "choice_label" => "name",
                "query_builder" => fn (ClientRepository $repository) => $repository
                    ->createQueryBuilderClientsByEmployee($employee)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired("employee");
        $resolver->setAllowedTypes("employee", [SalesPerson::class, Manager::class]);
        $resolver->setDefault("data_class", Customer::class);
    }
}
