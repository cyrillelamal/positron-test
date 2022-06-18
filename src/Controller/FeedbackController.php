<?php

namespace App\Controller;

use App\Domain\Feedback\Dto\CreateFeedbackDto;
use App\Domain\Feedback\Exception\BadFeedbackDataException;
use App\Domain\Feedback\UseCase\CreateFeedback;
use App\Form\FeedbackFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/feedback', name: 'feedback', methods: [Request::METHOD_GET, Request::METHOD_POST])]
class FeedbackController extends AbstractController
{
    private CreateFeedback $storeFeedback;
    private TranslatorInterface $translator;

    public function __construct(
        CreateFeedback      $storeFeedback,
        TranslatorInterface $translator,
    )
    {
        $this->storeFeedback = $storeFeedback;
        $this->translator = $translator;
    }

    /**
     * @throws BadFeedbackDataException all constraints are already handled by the form.
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(FeedbackFormType::class, new CreateFeedbackDto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            ($this->storeFeedback)($form->getData());

            $this->addFlash('success', $this->translator->trans('feedback.success'));
            return $this->redirectToRoute('index');
        }

        return $this->render('feedback/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
