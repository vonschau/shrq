<?php

namespace SHRQ\SymposiumBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('first_name', 'text', array('label' => 'First name'))
            ->add('last_name', 'text', array('label' => 'Last name'))
            ->add('address', 'textarea', array('label' => 'Address'))
            ->add('phone', 'text', array('label' => 'Phone'))
            ->add('email', 'text', array('label' => 'E-mail'))
            ->add('payment_type', 'choice', array('choices' => array(1 => 'PayPal', 2 => 'VISA')))
            ->add('ticket_type', 'choice', array('choices' => array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program')))
            ->add('paid', 'checkbox', array('required' => false))
            ->add('paid_date', 'datetime', array('required' => false, 'widget' => 'single_text'))
            ->add('paymentId', 'text', array('required' => false))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        	->add('paid')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('first_name')
            ->add('last_name')
            ->add('email')
            ->add('paid')
            ->add('paymentId')
            ->add('_action', 'actions', array('actions' => array('edit' => array())))
        ;
    }
}