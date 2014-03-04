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
use SHRQ\SymposiumBundle\WebPay\WebPayResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function homepageAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $payment_types = array(1 => 'PayPal', 2 => 'Credit Card');
        $ticket_types = array(1 => 'Symposium program', 2 => 'Symposium program + JtE1', 3 => 'JtE1', 4 => 'JtE1 + JtE2', 5 => 'Whole week program');

        $news = $this->getDoctrine()
            ->getRepository('SHRQSymposiumBundle:News')
            ->findBy(array(), array('publishDate' => 'DESC'));

        $programUpdates = $this->getDoctrine()
            ->getRepository('SHRQSymposiumBundle:ProgramUpdate')
            ->findBy(array(), array('publishDate' => 'DESC'));

        $documentManager = $this->container->get('doctrine_phpcr.odm.default_document_manager');

        $media = $documentManager->find(null, '/cms/simple/Media');
        $introduction = $documentManager->find(null, '/cms/simple/Introduction');
        $contacts = $documentManager->find(null, '/cms/simple/Contacts');
        $generalInfo = $documentManager->find(null, '/cms/simple/General info');

        return array(
            'user' => $user,
            'paymentTypes' => $payment_types,
            'ticketTypes' => $ticket_types,
            'news' => $news,
            'programUpdates' => $programUpdates,
            'introduction' => $introduction,
            'media' => $media,
            'contacts' => $contacts,
            'generalInfo' => $generalInfo,
        );
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
        $user->setPaidDate(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->getRequest()->getSession()->getFlashBag()->set('notice', 'Payment success.');

        return $this->redirect($this->generateUrl('shrq_symposium_default_homepage'));
    }

    /**
     * @Route("/payment-card-done")
     * @Template()
     */
    public function cardDoneAction(Request $request)
    {
        $kernel = $this->get('kernel');

        $response = new WebPayResponse ();
        $response->setPublicKey ($kernel->locateResource('@SHRQSymbposiumBundle/Resources/cert/muzo.signing_test.pem'));
        $response->setResponseParams ($request->query->getAll());
        $result = $response->verify ();

        if ($result) {
            $user->setPaid(true);
            $user->setPaidDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        } else {
            $this->getRequest()->getSession()->getFlashBag()->set('error', 'Payment error. Try again.');
        }

        return $this->redirect($this->generateUrl('shrq_symposium_default_homepage'));
    }
}