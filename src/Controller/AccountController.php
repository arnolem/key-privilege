<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/mon-compte")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/modifier-mot-de-passe", name="account_edit_password")
     */
    public function editPassword(
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response {
        $form = $this->createForm(EditPasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $user->setPassword($userPasswordEncoder->encodePassword($user, $form->get("plainPassword")->getData()));
            $user->setForgottenPasswordToken(null);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Votre mot de passe a été modifié avec succès.");

            return $this->redirectToRoute("account_edit_password");
        }

        return $this->render('account/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}