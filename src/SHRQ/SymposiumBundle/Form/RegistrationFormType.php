<?php

namespace SHRQ\SymposiumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SHRQ\SymposiumBundle\Form\Type\SimpleChoiceType;

class RegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', 'text')
            ->add('last_name', 'text')
            ->add('address', 'textarea')
            ->add('phone', 'text')
            ->add('email', 'text')
            ->add('username', 'hidden')
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('payment_type', new SimpleChoiceType(), array('expanded' => true, 'choices' => array(
                1 => '<img src="/bundles/shrqsymposium/img/ico-paypal.png" alt="">',
                2 => '<img src="/bundles/shrqsymposium/img/ico-card.png" alt="">')))
            ->add('ticket_type', 'choice', array('expanded' => true, 'choices' => array(
                1 => 'Symposium <br>program <br>(lectures, concert, <br>show) <span>€ 200</span>',
                2 => 'Symposium <br>program <br> with JtE1 <span>€ 450</span>',
                3 => 'JtE1 with Sahra <br>Saeeda <span>€ 250</span>',
                4 => 'JtE 1 and JtE 2 with <br>Sahra Saeeda <span>€ 450</span>',
                5 => '600 Eur - Whole <br>week program <span>€ 600</span>')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'registration',
        ));
    }

    public function getName()
    {
        return 'shrq_symposium_registration';
    }
}
