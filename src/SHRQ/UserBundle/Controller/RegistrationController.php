<?php

namespace SHRQ\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

class RegistrationController extends Controller
{

    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = 'fos_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:checkEmail.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $response = new RedirectResponse($this->container->get('router')->generate('fos_user_registration_confirmed'));
        $this->authenticateUser($user, $response);

        return $response;
    }

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

    /**
     * Authenticate a user with Symfony Security
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Response $response)
    {
        try {
            $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user,
                $response);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
}