<?php

namespace SHRQ\SymposiumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function homepageAction()
    {
        return array();
    }

    /**
     * @Route("/payment-done")
     * @Template()
     */
    public function doneAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $paypal = $this->get('paypal');

        $token = $request->query->get('token');
        $payerId = $request->query->get('PayerID');

        $payment = Payment::get($user->getPaymentId(), $paypal->getApiContext());

        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);

            $payment->execute($paymentExecution, $paypal->getApiContext());
            $user->setPaid(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->getRequest()->getSession()->getFlashBag()->set('notice', 'Payment success.');

        return $this->redirect($this->generateUrl('shrq_symposium_default_homepage'));
    }
}
