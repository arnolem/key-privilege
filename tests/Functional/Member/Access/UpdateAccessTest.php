<?php

declare(strict_types=1);

namespace App\Tests\Functional\Member\Access;

use App\Entity\Company\Member;
use App\Entity\User\Collaborator;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UpdateAccessTest extends WebTestCase
{
    public function testAsManagerIfAccessDenied(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_update", ["id" => 7]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAsManagerIfAccessAddIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        /** @var Member $menber */
        $member = $entityManager->find(Member::class, 3);

        $manager->getMembers()->add($member);

        $entityManager->flush();

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_update", ["id" => 11]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", [
            "access[firstName]" => "Prénom",
            "access[lastName]" => "Nom",
            "access[email]" => "new@email.com",
            "access[member]" => 3
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Collaborator $collaborator */
        $collaborator = $entityManager->getRepository(Collaborator::class)->findOneByEmail("new@email.com");

        $this->assertEquals("Prénom", $collaborator->getFirstName());
        $this->assertEquals("Nom", $collaborator->getLastName());
        $this->assertEquals("new@email.com", $collaborator->getEmail());
        $this->assertEquals(3, $collaborator->getMember()->getId());

        $client->followRedirect();

        $this->assertRouteSame("member_access_list");
    }

    /**
     * @dataProvider provideFailedData
     */
    public function testIfAccessAddIsFailed(array $formData, string $errorMessage): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("member_access_update", ["id" => 11]));

        $this->assertResponseIsSuccessful();

        $client->submitForm("Modifier", $formData);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            ".form-error-message",
            $errorMessage
        );
    }

    public function provideFailedData(): Generator
    {
        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "user+8@email.com"
            ],
            "Cette valeur est déjà utilisée."
        ];

        yield [
            [
                "access[firstName]" => "",
                "access[lastName]" => "Nom",
                "access[email]" => "new@email.com"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "",
                "access[email]" => "new@email.com"
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => ""
            ],
            "Cette valeur ne doit pas être vide."
        ];

        yield [
            [
                "access[firstName]" => "Prénom",
                "access[lastName]" => "Nom",
                "access[email]" => "fail"
            ],
            "Cette valeur n'est pas une adresse email valide."
        ];
    }
}
