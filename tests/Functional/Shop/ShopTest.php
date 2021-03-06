<?php

declare(strict_types=1);

namespace App\Tests\Functional\Shop;

use App\Entity\Order\Order;
use App\Entity\Shop\Product;
use App\Entity\User\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShopTest extends WebTestCase
{
    public function testIfShopIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        $client->request(Request::METHOD_GET, $urlGenerator->generate("shop_index"));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Catégorie 64");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->submitForm("Rechercher", [
            "page" => 1,
            "filter[keywords]" => "Produit",
            "filter[min]" => 100,
            "filter[max]" => 500,
            "filter[brand]" => 1
        ], Request::METHOD_GET);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("45 produits par page");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Tri A -> Z");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Tri Z -> A");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Valeur les moins cher");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Valeur les plus cher");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Panier");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testIfProductShowIsSuccessful(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Manager $manager */
        $manager = $entityManager->find(Manager::class, 1);

        $client->loginUser($manager);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get("router");

        /** @var Product $product */
        $product = $entityManager->find(Product::class, 1);

        $client->request(Request::METHOD_GET, $urlGenerator->generate("shop_product", [
            "slug" => $product->getSlug()
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());

        $client->clickLink("Ajouter au panier");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Panier");

        $client->clickLink("Plus");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(3, $order->getLines()->first()->getQuantity());

        $client->clickLink("Moins");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(2, $order->getLines()->first()->getQuantity());

        $client->clickLink("Moins");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(1, $order->getLines());
        $this->assertEquals(1, $order->getLines()->first()->getQuantity());

        $client->clickLink("Moins");

        $client->followRedirect();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get("doctrine.orm.entity_manager");

        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneBy([
            "state" => "cart",
            "user" => $manager
        ]);

        $this->assertCount(0, $order->getLines());
    }
}
