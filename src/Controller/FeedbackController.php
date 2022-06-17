<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackFormType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/feedback', name: 'feedback_')]
class FeedbackController extends AbstractController
{
    private Recaptcha3Validator $recaptchaValidator;
    private MailerInterface $mailer;

    public function __construct(
        Recaptcha3Validator $recaptchaValidator,
        MailerInterface     $mailer,
    )
    {
        $this->recaptchaValidator = $recaptchaValidator;
        $this->mailer = $mailer;
    }

    #[Route('/', name: 'index', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function index(Request $request): Response
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackFormType::class, $feedback);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            throw new LogicException('TODO: implement. Score: ' . $this->recaptchaValidator->getLastResponse()->getScore());
//            $email = (new Email())
//                ->from($this->getParameter('app.feedback.mail'))
//                ->to($form['email']->getData())
//                ->subject('Merci')
//                ->text('Salut !');
//
//            $this->mailer->send($email);

        }

        return $this->render('feedback/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
