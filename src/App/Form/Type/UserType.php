<?php
namespace App\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\ExecutionContext;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $options['users'];
        $builder
            ->add('name', TextType::class, array('attr'=>array('placeholder'=>'Name'),'constraints'=>array(new Assert\NotBlank(), new Assert\Length(array('min'=>3)))))
            ->add('email', EmailType::class, array('attr'=>array('placeholder'=>'Email'),'constraints'=>array(
                new Assert\NotBlank(),
                new Assert\Email(),
                new Assert\Callback(function($email, ExecutionContextInterface $context, $payload) use ($users){
                    if($users->findByEmail($email)){
                        $context->addViolation('Email already used');
                    }
                }))))
            ->add('password', RepeatedType::class, array(
                'type'=>PasswordType::class,
                'required' => true,
                'first_options'  => array('label' => 'Password', 'constraints'=>array(new Assert\NotBlank(), new Assert\Length(array('min'=>6)))),
                'second_options' => array('label' => 'Confirm Password'),
            ) );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(array('users'));
    }

    public function getName()
    {
        return 'user';
    }
}