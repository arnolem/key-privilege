<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Rules;
use App\Repository\QuestionRepository;
use App\Repository\RulesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faq-reglement", name="faq")
 */
class FaqController extends AbstractController
{
    /**
     * @param QuestionRepository<Question> $questionRepository
     * @param RulesRepository<Rules> $rulesRepository
     */
    public function __invoke(QuestionRepository $questionRepository, RulesRepository $rulesRepository): Response
    {
        return $this->render("ui/faq.html.twig", [
            "questions" => $questionRepository->findAll(),
            "rules" => $rulesRepository->getLastPublishedRules()
        ]);
    }
}
