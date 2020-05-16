<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DefaultController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('base.html.twig');
    }

    /**
     * Perform validation via symfony/form
     *
     * @param Request $request
     * @return View
     *
     * @Route("/with-form", name="symfony_form")
     */
    public function formComponent(Request $request)
    {
        // create the form using symfony/form
        $form = $this->createForm(CustomerType::class);

        // simulate form submission,
        // normally should be $form->handleRequest($request)
        $form->submit([
            'name' => '',
            'email' => 'foo',
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer = $form->getData();
            // save the customer
            // return the customer
            return View::create($customer, Response::HTTP_CREATED);
        }

        return View::create($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Perform validation via symfony/validator
     *
     * @param Request $request
     * @return View
     *
     * @Route("/just-validate", name="symfony_validator")
     */
    public function validatorComponent()
    {
        $customer = new Customer();
        $customer->setName('');
        $customer->setEmail('foo');

        // validate using symfony/validator
        $result = $this->validator->validate($customer);

        return View::create($result, Response::HTTP_BAD_REQUEST);
    }
}
