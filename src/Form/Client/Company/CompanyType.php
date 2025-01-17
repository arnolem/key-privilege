<?php

declare(strict_types=1);

namespace App\Form\Client\Company;

use App\Entity\Company\Client;
use App\Entity\Company\Member;
use App\Entity\User\Manager;
use App\Entity\User\SalesPerson;
use App\Repository\Company\MemberRepository;
use App\Repository\User\SalesPersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("manualDelivery", CheckboxType::class, [
                "label" => "Autoriser le client à saisir manuellement son adresse de livraison",
                "required" => false
            ])
            ->add("name", TextType::class, [
                "label" => "Raison sociale :",
                "empty_data" => "",
                "row_attr" => [
                    "class" => "mb-3"
                ]
            ])
            ->add("companyNumber", TextType::class, [
                "label" => "N° de SIRET :",
                "empty_data" => ""
            ]);

        /** @var SalesPerson|Manager $employee */
        $employee = $options["employee"];

        if ($employee instanceof Manager) {
            $memberOptions = [];

            if ($employee->getMembers()->count() > 1) {
                $memberOptions = ["group_by" => fn (SalesPerson $salesPerson) => $salesPerson->getMember()->getName()];
            }

            $builder->add("salesPerson", EntityType::class, $memberOptions + [
                "required" => true,
                "label" => "Commercial(e) :",
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "class" => SalesPerson::class,
                "choice_label" => fn (SalesPerson $salesPerson) => $salesPerson->getFullName(),
                "query_builder" => fn (SalesPersonRepository $repository) => $repository
                    ->createQueryBuilderSalesPersonsByManager($employee)
            ]);
        }

        if ($employee instanceof Manager && $employee->getMembers()->count() > 1) {
            $builder->add("member", EntityType::class, [
                "label" => "Adhérent :",
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "class" => Member::class,
                "choice_label" => "name",
                "query_builder" => fn (MemberRepository $repository) => $repository
                    ->createQueryBuilderMembersByManager($employee)
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired("employee");
        $resolver->setAllowedTypes("employee", [SalesPerson::class, Manager::class]);
        $resolver->setDefault("data_class", Client::class);
    }
}
