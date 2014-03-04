<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SHRQ\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends Controller
{
    /**
     * Show the user
     */
    public function showAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $payment_types = array(1 => 'PayPal', 2 => 'Credit Card');
        $ticket_types = array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program');

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user, 'paymentTypes' => $payment_types, 'ticketTypes' => $ticket_types));
    }

    /**
     * Edit the user
     */
    public function editAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->container->get('fos_user.profile.form');
        $formHandler = $this->container->get('fos_user.profile.form.handler');

        $process = $formHandler->process($user);
        if ($process) {
            $this->setFlash('fos_user_success', 'profile.flash.updated');

            return new RedirectResponse($this->getRedirectionUrl($user));
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Profile:edit.html.'.$this->container->getParameter('fos_user.template.engine'),
            array('form' => $form->createView())
        );
    }

    /**
     * Generate the redirection url when editing is completed.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fos_user_profile_show');
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

    /**
     * @Route("/paypal")
     * @Template()
     */
    public function paypalAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $payment_types = array(1 => 'PayPal', 2 => 'Credit Card');
        $ticket_types = array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program');
        $prices = array(1 => 200, 2 => 450, 3 => 250, 4 => 450, 5 => 600);
        $price = $prices[$user->getTicketType()];

        $paypal = $this->get('paypal');

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

        return $this->redirect($payment_url);
    }

    /**
     * @Route("/card")
     * @Template()
     */
    public function cardAction()
    {
        $kernel = $this->get('kernel');
        $user = $this->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $payment_types = array(1 => 'PayPal', 2 => 'Credit Card');
        $ticket_types = array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program');
        $prices = array(1 => 200, 2 => 450, 3 => 250, 4 => 450, 5 => 600);
        $price = $prices[$user->getTicketType()];

        $payment_id = 'RB'.rand(100000000, 999999999);


        $request = new WebPayRequest ();
        $request->setPrivateKey('private-key.pem', 'heslo');
        $request->setWebPayUrl('https://test.3dsecure.gpwebpay.com/rb/order.do');
        $request->setResponseUrl($this->generateUrl('shrq_symposium_default_cardDone', array(), true));
        $request->setMerchantNumber(2740301073);
        $request->setOrderInfo($payment_id,  /* webpay objednávka */
                               $user->getId(), /* interní objednávka */
                               $price);


        $user->setPaymentId($payment_id);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect($request->requestUrl ());
    }
}
