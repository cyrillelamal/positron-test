<?php

namespace App\Form;

use App\Domain\Feedback\Dto\CreateFeedbackDto;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;

class FeedbackFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => $this->getEmailConstraints(),
                'label' => 'feedback.email',
            ])
            ->add('name', TextType::class, [
                'label' => 'feedback.name',
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'constraints' => $this->getMessageConstraints(),
                'label' => 'feedback.message',
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'feedback.phone_number',
                'required' => false,
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'feedback',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateFeedbackDto::class,
        ]);
    }

    protected function getEmailConstraints(): array
    {
        return [
            new Required(),
            new NotBlank(),
            new Email(),
            new NotNull(),
        ];
    }

    protected function getMessageConstraints(): array
    {
        return [
            new Required(),
            new NotBlank(),
        ];
    }
}
