<?php

namespace SHRQ\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

class RegistrationController extends BaseController
{
    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $payment_types = array(1 => 'PayPal', 2 => 'Credit Card');
        $ticket_types = array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program');
        $prices = array(1 => 200, 2 => 450, 3 => 250, 4 => 450, 5 => 600);
        $price = $prices[$user->getTicketType()];

        if ($user->getPaymentType() === 1) {
            $paypal = $this->container->get('paypal');

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amountDetails = new Details();
            $amountDetails->setSubtotal($price);
            $amountDetails->setTax(number_format(0.034 * $price, 2));
            $amountDetails->setShipping('0.35');

            $amount = new Amount();
            $amount->setCurrency('EUR');
            $amount->setTotal(number_format($price + 0.034 * $price + 0.35, 2));
            $amount->setDetails($amountDetails);

            $transaction = new Transaction();
            $transaction->setAmount($amount);
            $transaction->setDescription('Payment for SHRQ Symposium on Eastern Culture Ticket.');

            $payment = new Payment();
            $payment->setIntent('sale');
            $payment->setPayer($payer);
            $payment->setTransactions(array($transaction));

            $redirectUrls = new RedirectUrls();
            $redirectUrls->return_url = $this->generateUrl('shrq_symposium_default_done', array(), true);
            $redirectUrls->cancel_url = $this->generateUrl('shrq_symposium_default_done', array(), true);
            $payment->setRedirectUrls($redirectUrls);

            $payment->create($paypal->getApiContext());

            $user->setPaymentId($payment->getId());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() === 'approval_url') {
                    $payment_url = $link->getHref();
                }
            }
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:confirmed.html.'.$this->getEngine(), array(
            'user' => $user,
            'payment_types' => $payment_types,
            'ticket_types' => $ticket_types,
            'prices' => $prices,
            'payment_url' => $payment_url
        ));
    }
}